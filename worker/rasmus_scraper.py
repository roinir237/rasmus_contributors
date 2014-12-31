from celery import Celery
import requests
import os
from subprocess import check_call
from datetime import datetime
import re

os.environ.setdefault('CELERY_CONFIG_MODULE', 'worker.celeryconfig')

app = Celery("rasmus_scraper")
app.config_from_envvar('CELERY_CONFIG_MODULE')

def get_contributors(package):
    package = packagist_to_gh(package)
    if package is not None:
        r = requests.get("https://api.github.com/repos/%s/contributors"%package, auth=(os.environ['GH_AUTH'], 'x-oauth-basic'))

        if r.status_code == 200:
            return [x["login"] for x in r.json()]
        elif r.status_code == 403:
            return int(r.headers["X-RateLimit-Reset"])


def get_packages():
    r = requests.get('https://packagist.org/packages/list.json')
    data = r.json()
    return data["packageNames"]

def packagist_to_gh(packegist_name):
    r = requests.get('https://packagist.org/p/%s.json'%packegist_name)
    try:
        homepage = r.json()["packages"][packegist_name].itervalues().next()["source"]["url"]
    except KeyError:
        return None
    gh_match = re.search('(?<=github.com/).+(?=.git)',homepage)
    return gh_match.group(0) if gh_match is not None else None


@app.task
def persist_packages(start_indx=0):
    packages = get_packages()
    packages = packages[start_indx:]

    for i, package in enumerate(packages):
        print package
        contributors = get_contributors(package)
        if type(contributors) is list:
            print check_call("SYMFONY_ENV=prod php app/console app:persist %s %s"%(package," ".join(contributors)), shell=True)
        elif type(contributors) is int:
            persist_packages.apply_async((start_indx+i,), eta=datetime.fromtimestamp(contributors))
            break
