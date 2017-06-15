# Gitlab Webhooks工作说明

### Linux机器所需权限配置：
1. 添加git用户，设置密码: `sudo adduser git`
2. 添加git到sudo组: `sudo adduser git sudo`
3. 使用git用户来管理文件: `sudo mkdir /www && sudo chown -R git:git /www`
4. 将www-data加到git用户组: `sudo usermod -aG git www-data`
5. 给www-data用来执行git pull的脚本，修改sudoers文件，`sudo vim /etc/sudoers`，添加: `www-data ALL=(git) NOPASSWD: /usr/bin/git`
6. 允许同组用户新增文件继承权限位: `sudo chmod -R g+s /www`
7. 用户的ssh配置文件必须为600权限: `sudo chmod 600 /home/git/.ssh/config`

### `gitpull.sh` Pull Choices? Current is 4
1. get branch info from ref ( [ref] => refs/heads/dev)
2. branch equals default branch (project.default_branch)
3. get commit message contains #letmeupdate or something else? 
4. checkout according to project's current branch?