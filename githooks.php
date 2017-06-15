<?php

function log_message($logfile, $message)
{
    file_put_contents($logfile, $message . PHP_EOL, FILE_APPEND);
}

function handle_error($logfile, $code, $message)
{
    http_response_code($code);
    log_message($logfile, $message);
    exit(0);
}

// 此处使用运行php的用户www-data来写入日志，确保这个目录是可被www-data写入的，否则是看不到日志文件的
$logfile = './logs/hooks-' . date('y-m-d') . '.log';
$scriptfile = './gitpull.sh';

$method = $_SERVER['REQUEST_METHOD'];

//$request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

$config = [
    'push-gitlab' => [
        // 最简单的规则，则使用当前项目名生成摘要作为token，echo push-gitlab | shasum
        'token' => '58ea529b3c1de83f8fbe4d4ff1723c6fe08a346f',
        // git checkout & pull的工作目录设置
        'work_dir' => '/www/push-gitlab/',
    ],
    // 添加多个项目，也就这样简单加配置咯
];

// 如果自己的gitlab服务器是有固定的外网ip，可以这样限制访问，如果没有就算了，不对此做验证。
$access_ip = array('0.0.0.0');

$client_ip = $_SERVER['REMOTE_ADDR'];

log_message($logfile, 'Request method:[' . $method . '] on [' . date('Y-m-d H:i:s') . '] from [' . $client_ip . ']');

if ($method != 'POST') {
    handle_error($logfile, 405, 'Method Not Allowed');
}

// 获取请求端发送来的信息，具体格式参见 GitLab 的文档：https://gitlab.com/help/user/project/integrations/webhooks
$json = file_get_contents('php://input');
if (empty($json)) {
    handle_error($logfile, 400, 'Bad Request: body empty');
}

$json_data = json_decode($json, true);
$name = $json_data['project']['name'];
$token_config = $config[$name]['token'];
$token_request = $_SERVER['HTTP_X_GITLAB_TOKEN'];

if ($token_request != $token_config) {
    // none of them matches
    handle_error($logfile, 403, "Forbidden: token mismatch");
}

if (!in_array($client_ip, $access_ip)) {
    handle_error($logfile, 403, "Forbidden: invalid ip");
}

// log request content

log_message($logfile, print_r($json_data, true));

$work_dir = $config[$name]['work_dir'];

$ref = $json_data['ref'];
//$default_branch = $json_data['project']['default_branch'] or "master";
// get current push event branch
//$branch = end(explode('/', $ref)) or "master";
$branch = end(explode('/', $ref));

$cmd = sprintf('bash %s -w %s -b %s 2>&1', $scriptfile, $work_dir, $branch);
log_message($logfile, "Exec: " . $cmd);

exec($cmd, $output, $return_var);

log_message($logfile, 'Exec Status: ' . $return_var . PHP_EOL . implode(PHP_EOL, $output) . PHP_EOL);

?>