Rasmus Contributors
===================

Aims:
-----
Develop a set of tools to find:

1. The shortest path between two github users that contributed to PHP packages (from packagist). 
2. Likely github users who might want to contribute to a package. 

Usage:
------
The solution is hosted on AWS and is available as an API at http://ec2-50-112-141-57.us-west-2.compute.amazonaws.com/. 

The API consists of two endpoints:

<b>1. GET api/path/{user1}/{user2}</b>: 
  
  Returns the shortest path between two users through PHP packages as an array of hops between users and packages they contributed to. For example http://ec2-50-112-141-57.us-west-2.compute.amazonaws.com/api/path/darkspider/zombor results in:
  ```
    {
      path: [
        "darkspider",
        [
          "app-skeleton/cache",
          "kohana/cache",
          "meerkat/kohana-cache"
        ],
        "zombor"
      ]
    }
  ```
<b>2. GET api/potentials/{package}</b>: 

  Returns an ordered list of potential future contributors to each package. A future potential contributor is a user who worked on a different PHP package with someone who currently contributes to the package. Thus the potential contributor is said to have a 'connection' to that package. The more connections a potential contributor has to a package the more likely he is to want to contribute to that package. 
  Each potential contributor is returned as a JSON object containing:
  - name: the name of the user
  - workedOn: a list of projects the user worked on and with whom 
  - connections: the total number of connections to that package
  For example: http://ec2-50-112-141-57.us-west-2.compute.amazonaws.com/api/potentials/kohana/minion returns (truncated):
```
{
  potentials: [
    {
      name: "wintersilence",
      workedOn: {
        scalephp/cli: {
          with: [
            "zombor",
            "shadowhand",
            "kemo",
            ...
          ] 
        }
      },
      connections: 19
    },
    ...
  ]
}
```

Implementation Details
-----------------------
<b>Scrapping and persisting data</b>
The scraper is written in python and can be found in worker/rasmus_scraper.py. Celery + supervisord are used to schedule and run the scraper. 

The scraper obtaines the list of packegist packages from https://packagist.org/packages/list.json. The github name of each package is then obtained from https://packagist.org/p/{packag}.json and finally the list of contributors is scraped from https://api.github.com/repos/{package_name}/contributors. 

The data is persisted to a mysql database (RDS instance). The DB contains tables for packages (name + id), users (name + id), and a user_package table to support the many-to-many relationship between names and packages. The DB is managed by doctrine bundle in the associated symfony app. A symfony console command app:persist (defined in src/AppBundle/Command/PersistCommand) is used to persist the packages and contributors to the database from the python script. 

<b>Serving the Data</b>
Symfony2 is used to serve up the data. The main API controller is in src/AppBundle/Controller/ApiController.php. The controller is just used to format the response that is returned from the grapher service (src/AppBundle/Grapher.php) which does all the interesting business logic.

The two main methods of grapher are Grapher#pathBetween which finds the shortest path between two users (using breadth first search), and Grapher#potentialContributors which gets a list of potential contributors to a package.

<b>Other</b>
- The app and worker services are started/deployed using capifony. 
