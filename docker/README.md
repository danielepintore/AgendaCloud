How to configure nginx container in the server:
- create the network with docker network create <network name>
- copy the docker-compose file under nginx.compose and launch the container
- launch the service that you want under the proxy by it's compose file, you can find an example under
service.compose file
- 