#!/bin/sh

$1/eclipse -noSplash -application org.eclipse.equinox.p2.publisher.UpdateSitePublisher -metadataRepository file:$2 -metadataRepositoryName "Babel Language Pack Metadata Repository" -artifactRepository file:$2 -artifactRepositoryName "Babel Language Pack Artifact Repository" -source $2
