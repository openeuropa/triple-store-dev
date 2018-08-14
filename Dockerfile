FROM tenforce/virtuoso:1.3.1-virtuoso7.2.2

RUN apt-get update \
    && apt-get install -y wget curl \
    && wget -O /tmp/resource-type.rdf http://publications.europa.eu/resource/cellar/07fa8597-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1
#    && curl --digest --user dba:dba --verbose --url 'http://localhost:8890/sparql-graph-crud-auth?graph-uri=http://localhost' -T /tmp/resource-type.rdf
