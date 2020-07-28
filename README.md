# Content layer development triple store

Triple storage containing default data to kick-start content layer local development
based on [OpenLink Virtuoso](https://virtuoso.openlinksw.com).

> ### Important
> The EU Publications Office released the official Digital Europa Thesaurus vocabulary. Prior to version 1.1.0 a local
> temporary copy of said vocabulary was being used. Please ensure to update to a newer version. More information
> in the [below section](#updating-from-version-100).

The following RDF triples will be imported once the service starts:

- [Corporate body classification](https://publications.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/corporate-body-classification/version-20180926-0)
- [Corporate body](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/corporate-body/version-20200624-0)
- [Country](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/country/version-20200624-0)
- [Digital Europa Thesaurus](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/det/version-1.2.0)
- [EuroVoc Thesaurus](https://op.europa.eu/en/web/eu-vocabularies/th-dataset/-/resource/dataset/eurovoc/version-20200630-0)
- [EU Programme](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/eu-programme/version-20200624-0)
- [Language](https://publications.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/language/version-20200624-0)
- [Organization type](https://publications.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/organization-type/version-20170920-0)
- [Place](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/place/version-20200624-0)
- [Public event type](https://publications.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/public-event-type/version-20180926-0)
- [Resource type](https://op.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/resource-type/version-20200624-0)
- [Target audience](https://publications.europa.eu/en/web/eu-vocabularies/at-dataset/-/resource/dataset/target-audience/version-20180620-0)

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
version: '2'

services:
  triple-store:
    image: openeuropa/triple-store-dev
    ports:
      - 8890:8890
```

For more information about Docker Compose configuration check the parent Docker image
[Tenforce Virtuoso](https://hub.docker.com/r/tenforce/virtuoso/).

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
