# :warning: This repository is archived

Development has moved to new [triple store repository](https://git.fpfis.tech.ec.europa.eu/fpfis/triple-store).

# Content layer development triple store

Triple storage containing default data to kick-start content layer local development
based on [OpenLink Virtuoso](https://virtuoso.openlinksw.com).

> ### Important
> The EU Publications Office released the official Digital Europa Thesaurus vocabulary. Prior to version 1.1.0 a local
> temporary copy of said vocabulary was being used. Please ensure to update to a newer version. More information
> in the [below section](#updating-from-version-100).

The following RDF triples will be imported once the service starts:

- [Corporate body classification](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/corporate-body-classification&version=20240925-0)
- [Corporate body](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/corporate-body&version=20250319-0)
- [Country](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/country&version=20230614-0)
- [Digital Europa Thesaurus](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/det&version=2.11.0)
- [EuroVoc Thesaurus](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/eurovoc&version=4.21)
- [EU Programme](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/eu-programme&version=20241211-0)
- [Language](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/language&version=20240925-0)
- [Organization type](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/organization-type&version=20240612-0)
- [Place](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/place&version=20250319-0)
- [Public event type](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/public-event-type&version=20221214-0)
- [Resource type](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/resource-type&version=20250319-0)
- [Target audience](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/target-audience&version=20240925-0)
- [Sustainable Development Goals](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/sdg&version=20200930-0)
- [Human sex](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/human-sex&version=20241211-0)
- [Role](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/role&version=20241211-0)
- [Role qualifier](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/role-qualifier&version=20250319-0)
- [Strategic priority](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/strategic-priority&version=20241201-0)
- [European Commission web presence classes](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/com-web-presence&version=4.0)
- [EU political leader name](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/political-leader&version=20241201-0)
- [Position grade](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/position-grade&version=20210929-0)
- [Position type](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/position-type&version=20210929-0)
- [Procurement procedure type](https://op.europa.eu/en/web/eu-vocabularies/dataset/-/resource?uri=http://publications.europa.eu/resource/dataset/procurement-procedure-type&version=20240925-0)

New default content can be added to [`robo.yml`](./robo.yml) as shown below:

```
data:
  - name: "corporate-body"
    graph: "http://publications.europa.eu/resource/authority/corporate-body"
    url: "http://publications.europa.eu/resource/cellar/07e1a665-2b56-11e7-9412-01aa75ed71a1.0001.10/DOC_1"
    format: "rdf"
```

Value of `format:` property can be either `rdf` or `zip`. If `zip` then an additional `file:` property is expected
containing the archived RDF file name as shown below:

```
data:
  - name: "eurovoc-thesaurus"
    graph: "http://publications.europa.eu/resource/dataset/eurovoc"
    url: "http://publications.europa.eu/resource/cellar/9f2bd600-ae7b-11e7-837e-01aa75ed71a1.0001.09/DOC_1"
    file: "eurovoc_in_skos_core_concepts.rdf"
    format: "zip"
```

## Build and run

Build:

```
$ docker build . -t openeuropa/triple-store-dev
```

Run:

```
docker run --name=triple-store-dev -p 8890:8890 openeuropa/triple-store-dev
```

Visit the RDF storage at: http://localhost:8890

## Available commands

Fetch remote data:

```
$ docker exec triple-store-dev ./vendor/bin/robo fetch
```

Purge all data, to be ran before `import`:

```
$ docker exec triple-store-dev ./vendor/bin/robo purge
```

Import default data:

```
$ docker exec triple-store-dev ./vendor/bin/robo import
```

All commands above accept the following options:

```
--import-dir[=IMPORT-DIR]        Data import directory. [default: "./import"]
--host[=HOST]                    Virtuoso backend host. [default: "localhost"]
--port[=PORT]                    Virtuoso backend port. [default: 1111]
--username[=USERNAME]            Virtuoso backend username. [default: "dba"]
--password[=PASSWORD]            Virtuoso backend password. [default: "dba"]
```

Passing these options to a command will override their related default value set in `robo.yml`.

Option values can also be set using the following environment variables:

```
IMPORT_DIR
DBA_HOST
DBA_PORT
DBA_USERNAME
DBA_PASSWORD
```

Default values set via environment variables will override values set in `robo.yml`.

## Working with Docker Compose

In Docker Compose declare service as follow:

```
services:
  triple-store:
    image: openeuropa/triple-store-dev
    ports:
      - 8890:8890
```

For more information about Docker Compose configuration check the parent Docker image
[OpenLink Virtuoso Open Source Edition](https://hub.docker.com/r/openlink/virtuoso-opensource-7).

Additionally, the environment variable `SPARQL_UPDATE=true` can be defined to allow writing to the triple store.
Please note that only `true` as value is supported. Any other value will be the same as ommitting the variable.

```
services:
  triple-store:
    image: openeuropa/triple-store-dev
    environment:
      - SPARQL_UPDATE=true
```

In order to test a specific branch of the `triple-store-dev` image follow the steps below:

In the `docker-compose.yml` of the testing project (i.e. the `oe_content` module) use:

```
  sparql:
    build: /path/to/your/local/triple-store-dev/checkout
#    image: openeuropa/triple-store-dev
    environment:
```

Given that all your services are down, to rebuild run the following:

```
docker-compose build --force-rm --no-cache sparql
docker-compose up -d
```

## Changelog

The changelog is generated using a local docker installation which installs
[muccg/docker-github-changelog-generator](https://github.com/muccg/docker-github-changelog-generator)

This reads the [Github API](https://api.github.com/repos/openeuropa/triple-store-dev) for the required repository and
writes the CHANGELOG.md to the root of the repository.

**Prerequisites**

- Local Docker machine running.
- A [Github Access Token](https://github.com/settings/tokens) should be generated and exported (or written to ~/.gitconfig)
  as `CHANGELOG_GITHUB_TOKEN=<YOUR TOKEN HERE>`

Before tagging a new release export the following:

```bash
export CHANGELOG_GITHUB_TOKEN=<YOUR TOKEN HERE>
export CHANGELOG_FUTURE_RELEASE=<YOUR FUTURE TAG RELEASE e.g '1.0.0'>
```

The changelog can then be generated by running:

```bash
composer run-script changelog
```

## Updating from version 1.0.0

A temporary local copy of the Digital Europa Thesaurus was provided until version 1.0.0 of this repository. When the
official version of the vocabulary was released, it was noticed that a wrong concept scheme URI was present in the local
copy.

In order to update to version 1.1.0 or later, change any references of the old concept scheme URI
`http://data.europa.eu/uxp` to the correct one
```
http://data.europa.eu/uxp/det
```
