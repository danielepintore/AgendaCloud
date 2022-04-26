# How to build the image

To build the image you need to go in the service/agendacloud folder from the home of the agendacloud user. In this directory you have the Dockerfile and a build directory. In the build directory put the output of gulp build, (can be found in the build folder) and then execute this:

```bash
docker build -t agendacloud:latest .
```

This command will create the image with the tag agendacloud:latest

If you want to push this image to DockerHub you need to log in with

```bash
docker login
```

And then retag the image

```bash
docker tag agendacloud:latest dockerhubusername/agendacloud:latest
```

And finally push the image

```bash
docker push dockerhubusername/agendacloud:latest
```