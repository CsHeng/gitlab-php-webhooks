#!/usr/bin/env bash
# *****************************************************************************
# @author CsHeng
# @2017.05.27
#
# 所需权限配置：
# 1、给www-data用来执行git pull的脚本，需要在sudoers指定 `www-data ALL=(git) NOPASSWD: /usr/bin/git`
# 2、使用git用户来管理文件：sudo chown -R git:git /www
# 3、将www-data加到git用户组：sudo usermod -aG git www-data
# 4、允许同组用户创建继承权限：sudo chmod -R g+s /www/
# 5、git用户的git的配置文件必须为600权限：chmod 600 /home/git/.ssh/config
#
# Pull Choices? Current is 4
# 1. get branch info from ref ( [ref] => refs/heads/dev)
# 2. branch equals default branch (project.default_branch)
# 3. get commit message contains #letmeupdate or something else?
# 4. checkout according to project's current branch?
#
# 选项：
#       -w git word dir
#       -b git branch, default is master
#
# *****************************************************************************

CWD=$(cd "$(dirname "$0")"; pwd)
WORKDIR=
BRANCH_PUSH=
while getopts "w:b:" arg
do
	case ${arg} in
		w)
		WORKDIR=${OPTARG}
		;;
		b)
		BRANCH_PUSH=${OPTARG}
		;;
		?)
		echo "unknown argument"
		exit 1;
		;;
		esac
done

# 如果没有指定目录或push分支为空，直接退出
if [ -z ${WORKDIR} ] || [ ! -d ${WORKDIR} ] || [ -z ${BRANCH_PUSH} ] ; then
    echo 'workdir empty' && exit 0
fi

# enter workdir
echo "cd" ${WORKDIR} ":" && cd ${WORKDIR}

BRANCH_ORIGINAL=$(git symbolic-ref --short -q HEAD)

# only exec checkout & pull if branch match
if [ ${BRANCH_PUSH} != ${BRANCH_ORIGINAL} ] ; then
    echo "branch mismatch, ignored" && exit 0
fi

echo "git checkout"
sudo -u git /usr/bin/git checkout -f ${BRANCH_ORIGINAL}

echo "git pull"
sudo -u git /usr/bin/git pull origin ${BRANCH_ORIGINAL}


