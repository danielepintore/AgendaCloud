# How to setup the VM

To setup the VM we can use the custom image available on oracle cloud infrastructure. But if you need to build it for another architecture these are the steps needed.

## Update the repos and the system

The command needed to that can vary based on the operating system that you are using but in this guide we are using ubuntu 20.04.

**Log as root** user and type

```bash
apt update && apt upgrade -y
```

or if you can’t use the root user you can use `sudo`

```bash
sudo apt update && sudo apt upgrade -y
```

Then reboot your system with `reboot`

Now that you have an updated system.

## Create users

In this step we need to create 2 users: `agendacloud` and `config` user.  In this guide we suppose that you are logged as root user if you aren’t just add sudo before each command. To create the users yo need to type:

```bash
adduser agendacloud
adduser config
```

The adduser command will provide simple prompts and then it will create the user with their default groups

### Lock password login

In order to lock the login via password you need to type:

```bash
passwd -l agendacloud
passwd -l config
```

### Disable shell access for config user

```bash
usermod config -s /bin/false
```

### Add administrative privileges to agendacloud

```bash

usermod -aG sudo agendacloud
```

## Modify sudo configuration to disable the password prompt for agendacloud

In general you should modify the sudoers file with this line

```bash
agendacloud ALL=(ALL) NOPASSWD:ALL
```

But in the oracle cloud infrastructure image you can edit the file `/etc/sudoers.d/90-cloud-init-users`  and remove the role for ubuntu and add for agendacloud (just change the username)

## Create and assign groups

Now we need to create a group to manage all users that should use only sftp service

```bash
groupadd sftpusers
```

Now we need to add the config user to that group

```bash
usermod -aG sftpusers config
```

We also need to add to agendacloud user the config group in order to access to the configs file created by that user

```bash
usermod -aG config agendacloud
```

Keep in mind that the config group has been created by the adduser command

## Add the folder for sftp users

Now that we have all users and groups setted up we can create the directory for the sftp user config:

```bash
cd /
mkdir -p /sftp/agendacloud-configs/configs
# we need to make sure that the sftp and the agendacloud-configs directory 
# are owned by root user (in order to create a chroot jail)
chown -R root: /sftp
chmod 755 /sftp/agendacloud-configs
chown config: /sftp/agendacloud-configs/configs
chmod 770 /sftp/agendacloud-configs/configs
# make the file and dir created insie to inert the group 
find /sftp/agendacloud-configs -type d -exec chmod g+s {} \;
```

### Create the config link in agendacloud home

```bash
ln -s /sftp/agendacloud-configs/configs/ /home/agendacloud/configs
# adjust ownership if this is run by root
chown -h agendacloud: /home/agendacloud/configs
```

## Configure the firewall

To configure the firewall you can type:

```bash
ufw app list
```

This command gives a list of available apps, you can expect something like this:

```
Available applications:
  OpenSSH
```

now we need to allow OpenSSH, and enable the firewall

```bash
ufw allow OpenSSH
ufw enable
```

Now if you type `ufw status` you can see this output:

```
ufw status

Status: active

To                         Action      From
--                         ------      ----
OpenSSH                    ALLOW       Anywhere
OpenSSH (v6)               ALLOW       Anywhere (v6)
```

## Configure SSH

### Generate key files

Generate if you don’t have a ssh key pair using `ssh-keygen` . Note: you can pass the optional parameter `-b 4096` to increase the size of the key, at the time of this documentation `ssh-keygen` will provide a 3072-bit RSA key pair, which is secure enough for most use cases. The `ssh-keygen` command will provide a simple guided script to create your key files. Now we have a public (id_rsa.pub) and a private (id_rsa) key files. **Note: keep the private key private**

### Generic installation of keys

Now that you have your key files you need to add them to your users. To do that we need to access to the VM, then go to our user home directory, create if it doesn’t exist the `.ssh` directory. Inside that directory we need to create a file named `authorized_keys` , in which we put our public key generated before (we can cat it and then paste via a text editor like vim or nano). Once we have done that we need to make sure that the .ssh directory and all files inside it belongs to the correct user (the one of the home directory)

```bash
# runned in the home directory of user1
chown -R user1:user1 .ssh
```

and then we want to remove all “group” and “other” permissions for the `.ssh/` directory.

```bash
chmod -R go= .ssh
```

Double check that the permission of the .ssh directory are 700 and the ones of the `authorized_keys` are 600 and if they are different use chmod to change them

```bash
chmod 700 .ssh
chmod 600 authorized_keys
```

### What should we do in our setup

In our specific case we need to type these commands:

```bash
# go to the home directory of agendacloud
cd /home/agendacloud
mkdir .ssh
# add ssh public key
nano .ssh/authorized_keys
# sets right ownership and permissions
chown -R agendacloud:agendacloud .ssh
chmod 700 .ssh
chmod 600 .ssh/authorized_keys
chmod -R go= .ssh

# repeat the process for config user
# go to the home directory of config
cd /home/config
mkdir .ssh
# add ssh public key
nano .ssh/authorized_keys
# sets right ownership and permissions
chown -R config:config .ssh
chmod 700 .ssh
chmod 600 .ssh/authorized_keys
chmod -R go= .ssh
```

### Tweak sshd_config file

We need to edit the sshd_config file to disable passwords logins and allow only the key login. This file in ubuntu 20.04 is located under `/etc/ssh/sshd_config`

```bash
nano /etc/ssh/sshd_config
```

Under authentication section we need to add/uncomment these lines:

```
LoginGraceTime 30
PermitRootLogin no
PubkeyAuthentication yes
AuthorizedKeysFile      .ssh/authorized_keys
PasswordAuthentication no
ChallengeResponseAuthentication no
UsePAM yes
```

If these line already exists just edit them in the configuration. **Note: do not add the line 2 times.**

**Tip: also add the HostKey directive to improve security (just remove the # from the config)**

We need to make another change in the `sshd_config` file, this change is needed to allow sftp access and disable ssh login to users that are in the group `sftpusers`

Go to the end of the file and then add these lines: (if some of them are already present just delete or comment them), check also if another sftp subsystem exist and comment or delete it

```
Subsystem sftp internal-sftp
    Match group sftpusers
    ChrootDirectory /sftp/agendacloud-configs
    AuthorizedKeysFile /home/%u/.ssh/authorized_keys
    X11Forwarding no
    PasswordAuthentication no
    AllowTcpForwarding no
    PermitTunnel no
    AllowAgentForwarding no
    ForceCommand internal-sftp
```

Now we want to restart ssh service

```bash
systemctl restart ssh
systemctl restart sshd
```

## Install docker

We need to install a few prerequisite packages which let `apt` use packages over HTTPS:

```bash
apt install apt-transport-https ca-certificates curl software-properties-common
```

Then we need to add the GPG key for the official Docker repository into the system:

```bash
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
```

Add the Docker repository to APT sources: (specify your correct architecture, in this case i’m using arm64 but you may be using `amd64`). To get your current arch you can type: `uname -m` . If you are using an arm64 machine you can see this output `aarch64` but they are arm64.

```bash
add-apt-repository "deb [arch=arm64] https://download.docker.com/linux/ubuntu focal stable"
```

Make sure you are about to install from the Docker repo instead of the default Ubuntu repo:

```bash
apt-cache policy docker-ce

# You’ll see output like this, although the version number for Docker may be different:
docker-ce:
  Installed: (none)
  Candidate: 5:19.03.9~3-0~ubuntu-focal
  Version table:
     5:19.03.9~3-0~ubuntu-focal 500
        500 https://download.docker.com/linux/ubuntu focal/stable amd64 Packages
```

Then install docker

```bash
apt install docker-ce
```

And enable it

```bash
systemctl enable docker
systemctl status docker
```

Add agendacloud user to the docker group to execute docker without sudo

```bash
sudo usermod -aG docker agendacloud
```

To apply this setting you need to re-login to the instance

## Create docker network to interconnect containers to nginx-proxy

To interconnect all containers through the proxy you need to create a network, all the docker-compose files use a network called `proxy-net` , then we should create it using the following command:

```bash
docker network create proxy-net
```

## Install docker-compose

To install docker-compose really depends on the architecture, in this guide I’ll cover for the ARM64 architecture. You can find more at [this](https://docs.docker.com/compose/install/) link

```bash
mkdir -p /usr/local/lib/docker/cli-plugins
curl -SL https://github.com/docker/compose/releases/download/v2.4.1/docker-compose-linux-aarch64 -o /usr/local/lib/docker/cli-plugins/docker-compose
chmod +x /usr/local/lib/docker/cli-plugins/docker-compose
docker compose version
```

## Setting up the agendacloud home directory

The last thing to do is to set up the agendacloud directory to respect the scheme rappresented in the “VM setup” page. All docker-compose files can be found in the repository.

## Setup nginx-proxy

To start the nginx-proxy containers use the docker-compose file available in the repository

## Setup portainer

To start the portainer container use the docker-compose file available in the repository. Then log in to the portainer interface (port 9443) and create a custom template using the docker-compose file of the agendacloud app. Make sure to specify this:

`- /home/agendacloud/configs/COMPANY_NAME/env:/var/www/html/.env` in the volume of the app image and this:

`- /home/agendacloud/configs/base.sql:/docker-entrypoint-initdb.d/00-dump.sql` on the database service

## Remove unused users

If there are unused users remove them with `userdel --remove USERNAME`

In case of an instance created with OCI (Oracle Cloud Infrastructure):

```bash
userdel --remove ubuntu
userdel --remove opc
```