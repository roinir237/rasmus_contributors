from datetime import timedelta
import os

BROKER_URL = os.environ['BROKER_URL']
CELERY_RESULT_BACKEND = os.environ['CELERY_RESULT_BACKEND']

CELERY_TASK_SERIALIZER = 'json'
CELERY_RESULT_SERIALIZER = 'json'
CELERY_ACCEPT_CONTENT=['json']
CELERY_TIMEZONE = 'Europe/London'
CELERY_ENABLE_UTC = True

CELERYBEAT_SCHEDULE = {
    'tweet-every-3-day': {
        'task': 'worker.rasmus_scraper.persist_packages',
        'schedule': timedelta(days=3)
    }
}
