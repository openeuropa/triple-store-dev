-- Add proper permissions for the SPARQL endpoint.
-- See https://community.openlinksw.com/t/virtuoso-user-rdf-graph-level-access-to-rdf-quad-store/4147
-- Do not change quotes on the following lines, and do not add an empty line at the end.
DB.DBA.RDF_DEFAULT_USER_PERMS_SET('SPARQL', 7, 0);
GRANT SPARQL_UPDATE TO "SPARQL";