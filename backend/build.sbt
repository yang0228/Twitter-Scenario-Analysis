name := "twitter"

version := "0.1"

scalaVersion := "2.10.3"

scalacOptions ++= Seq("-unchecked", "-deprecation")

resolvers ++= Seq(
  "spray repo" at "http://repo.spray.io",
  "Sonatype Snapshots" at "http://oss.sonatype.org/content/repositories/snapshots/"
)

libraryDependencies ++= Seq(
  "org.twitter4j" % "twitter4j-stream" % "4.0.1",
  "org.gnieh" % "sohva-client_2.10" % "0.6-SNAPSHOT",
  "com.typesafe.akka" %% "akka-cluster" % "2.3.2",
  "com.typesafe.akka" %% "akka-contrib" % "2.3.2",
  "com.typesafe.akka" %% "akka-multi-node-testkit" % "2.3.2"
)
