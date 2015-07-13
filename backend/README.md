> Back-end Execution
* Please make sure Scala 2.10, SBT, CouchDB are installed correctly.
* Don't remove both project and target folder. They are used to generate code

There are a few configuration files under backend/src/main/resources/ need to be noticed.
• ”application.conf” & ”stats”: Akka Cluster Actor System
• ”location.conf”: the boundingboxes of the city
• ”Oauth.conf”: Twitter develop ID
• ”scenario.conf”: CouchDB views
• ”slave.conf”: the IP Address of computing instance
• ”webserver”: the IP Address of web server



## Harvester Instance
1) Change ”slave.conf”
Format: slave=IP1 IP2 IP3 IP4...
2) sbt
3) runMain harvest.MelStreamer
or runMain harvest.LAStreamer
The Harvester can also collect tweets from user-defined city:
1) Change ”location.conf”
Format: CITYNAME=BOUNDINGBOX 
e.g. la=-118.668167 33.703732 -118.155212 34.336781
2) sbt
3) runMain harvest.LocationStreamer la



## Computing instance
1) Go to ”application.conf”, change host name to the IP of that instance
2) Change seed-nodes to all the computing instances’ IP address
3) sbt
4) runMain stats.StatsComputeNode PORT
The port number is depending on the ”application.conf”. By default, it’s 2551. 
i.e. runMain stats.StatsComputeNode 2551



## Merging instance
1) Go to ”application.conf”, change host name to the IP of that instance
2) Change seed-nodes to all the computing instances’ IP address
3) The merging node application supports adding new scenarios(views), the new 
views should be created under ”_design/analysis”. Then change ”scenarios.conf”.
Format: DBNAME scenario=V:GL V:GL ... V:GL V for view name, GL for group level. 
e.g.la scenario=follower friend cmp:1 get activiness day:1 ...
4) sbt
5) runMain stats.StatsMergeNode CITY1 CITY2 ...CITYN
FYI, CITYs should be the name of the database stores on the computing instances. 
And CITYs are the DBs you want to merge. You can use one instance to merge all 
cities’ database. e.g. runMain stats.StatsMergeNode mel la.
Or deploy more instances to merge result parallelly.

