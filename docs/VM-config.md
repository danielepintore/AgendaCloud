# VM config

## Users

Each VM using the image is ready to host the web application, it comes with a set of users. The authentication is available using only key files.

Table of users with their purpose:

| Username | Purpose |
| --- | --- |
| agendacloud | This is the main user, it is used to do all the maintenance of the vm, it can build images and start docker containers, it is in the sudo group |
| config | This user doesn’t have an ssh access it can be used to access to the configurations directory |

## Home directory

The home directory of the main user (agendacloud) has this scheme:

—>| README

| configs

| docker-compose (legacy) now it isn’t needed cause docker compose v2

| services

The `config` directory is a symbolic link to `/sftp/agendacloud-configs/configs` and contains a `base.sql` file, used to populate the database (with a clean config) and a `env_sample` file that contains the base configuration variables for the instances. In this directory should be created other dirs, one per company. Inside each subdir in this directory there must be a `env` file, using the `env_sample` as a sample.

## The docker-compose directory has been deprecated and you should use docker-compose v2

The `docker-compose` directory contains a python3 virtual environment (venv) needed if you want to user docker-compose. The venv is needed because this VM image is built for ARM64 architecture and docker-compose v1 isn’t compatible with this architecture. If you want to use docker-compose you need to type: **`. docker-compose` note that the dot at the beginning is important** ⚠️ . Once you type this command the venv should be loaded, you can see it because at the beginning of the prompt there is `(docker-compose)`. When the venv is loaded you can use the docker-compose command normally. To exit from the venv you need to digit: `deactivate`

The `services` directory contains 3 subfolders:

- `agendacloud`

  This directory contains a sub-directory called `image` . This subdirectory is used to build the image, for more information please refer to the “**How can I build the image**” section. In the agendacloud there are 3 docker-compose files. The one that have build in the name is used to create the image and deploy the service in the same time, it is useful in developer environment. Note that to use the docker-compose-build.yaml you need the Dockerfile contained in the `image` directory. The other docker compose file is used to deploy the instance from the image on dockerhub (it uses the same configuration scheme of the one accessible via portainer).

- `nginx-proxy`

  The `nginx-proxy` directory contains a docker compose file that is used to start the nginx-proxy container. This container start a nginx web server and automatically search for new containers that should be exposed by searching for `VIRTUAL_HOST` and `LETSENCRYPT_HOST` environment variables. The last variable is needed only if we want to use the `acme-companion` container to provide ssl certificates. This docker-compose file also starts the `acme-companion` container. The `acme-companion` container is used to manage the ssl certificate of the instances

- `portainer`

  The portainer directory contains a docker compose file that is used to start the instance of portainer using the latest image available and exposing the port 9433. The service uses a self-made ssl certificate and a warning in the browser can be seen. The username and the passwords used to access the portainer instance ca be retrieved in bitwarden


## Config directory

The config directory, accessible via sftp by the user config or via the symbolic link in the home of agendacloud user is configured in a way that all new files/folder created inside keep the same group (**config**). In this way if you create a file from the config user or from the agendacloud user the file created is accesible to both of them.

### Structure

This directory contain by default 2 files: `base.sql` and `env_sample` . The `base.sql` file shouldn’t be moved because it is used as default database template in the docker compose files. the `env_sample` file can be moved but is suggested to keep it in the config directory.

This folder should keep a simple structure like this: config → COMPANY_NAME → env

The env file can be copied by the config folder (env_sample), this file provides a basic configuration of the PHP application. **Note: The instance will not work if you don’t set the db credentials.**