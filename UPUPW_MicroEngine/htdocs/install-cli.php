<?php
/**
php.exe -f install-cli.php root@123456@127.0.0.1@we712314
*/
error_reporting(0);
@set_time_limit(0);
@set_magic_quotes_runtime(0);
ob_start();
define('IA_ROOT', str_replace("\\",'/', dirname(__FILE__)));

$connect_str = $argv[1];
list($username, $password, $host, $database) = explode('@', $connect_str);

$link = mysql_connect($host, $username, $password);
if(empty($link)) {
	$error = mysql_error();
	if (strpos($error, 'Access denied for user') !== false) {
		$error = '您的数据库访问用户名或是密码错误. <br />';
	} else {
		$error = iconv('gbk', 'utf8', $error);
	}
} else {
	mysql_query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
	mysql_query("SET sql_mode=''");
	if(mysql_errno()) {
		$error = mysql_error();
	} else {
		$query = mysql_query("SHOW DATABASES LIKE  '{$database}';");
		if (!mysql_fetch_assoc($query)) {
			if(mysql_get_server_info() > '4.1') {
				mysql_query("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8", $link);
			} else {
				mysql_query("CREATE DATABASE IF NOT EXISTS `{$database}`", $link);
			}
		}
		$query = mysql_query("SHOW DATABASES LIKE  '{$database}';");
		if (!mysql_fetch_assoc($query)) {
			$error .= "数据库不存在且创建数据库失败. <br />";
		}
		if(mysql_errno()) {
			$error .= mysql_error();
		}
	}
}
if (!empty($error)) {
	echo $error;
	exit;
}

mysql_select_db($database);
$query = mysql_query("SHOW TABLES LIKE 'ims_%';");
if (mysql_fetch_assoc($query)) {
	echo 'Database is not empty.';
	exit;
}

$pieces = explode(':', $host);
$port = !empty($pieces[1]) ? $pieces[1] : '3306';
$config = local_config();
$cookiepre = local_salt(4) . '_';
$authkey = local_salt(8);
$config = str_replace(array(
	'{db-server}', '{db-username}', '{db-password}', '{db-port}', '{db-name}', '{db-tablepre}', '{cookiepre}', '{authkey}', '{attachdir}'
), array(
	$host, $username, $password, $port, $database, 'ims_', $cookiepre, $authkey, 'attachment'
), $config);
$verfile = IA_ROOT . '/framework/version.inc.php';
$dbfile = IA_ROOT . '/data/db.php';
$dat = include $dbfile;
if (empty($dat)) {
	echo 'Installtion data error.';
	exit;
}
foreach($dat['schemas'] as $schema) {
	$sql = local_create_sql($schema);
	local_run($sql);
}
foreach($dat['datas'] as $data) {
	local_run($data);
}
$salt = local_salt(8);
$password = sha1("we7.cc-{$salt}-{$authkey}");
mysql_query("INSERT INTO ims_users (username, password, salt, joindate) VALUES('admin', '{$password}', '{$salt}', '" . time() . "')");
local_mkdirs(IA_ROOT . '/data');
file_put_contents(IA_ROOT . '/data/config.php', $config);
touch(IA_ROOT . '/data/install.lock');

function local_salt($length = 8) {
	$result = '';
	while(strlen($result) < $length) {
		$result .= sha1(uniqid('', true));
	}
	return substr($result, 0, $length);
}

function local_config() {
	$cfg = <<<EOF
<?php
defined('IN_IA') or exit('Access Denied');

\$config = array();

\$config['db']['master']['host'] = '{db-server}';
\$config['db']['master']['username'] = '{db-username}';
\$config['db']['master']['password'] = '{db-password}';
\$config['db']['master']['port'] = '{db-port}';
\$config['db']['master']['database'] = '{db-name}';
\$config['db']['master']['charset'] = 'utf8';
\$config['db']['master']['pconnect'] = 0;
\$config['db']['master']['tablepre'] = '{db-tablepre}';

\$config['db']['slave_status'] = false;
\$config['db']['slave']['1']['host'] = '';
\$config['db']['slave']['1']['username'] = '';
\$config['db']['slave']['1']['password'] = '';
\$config['db']['slave']['1']['port'] = '3307';
\$config['db']['slave']['1']['database'] = '';
\$config['db']['slave']['1']['charset'] = 'utf8';
\$config['db']['slave']['1']['pconnect'] = 0;
\$config['db']['slave']['1']['tablepre'] = 'ims_';
\$config['db']['slave']['1']['weight'] = 0;

\$config['db']['common']['slave_except_table'] = array('core_sessions');

// --------------------------  CONFIG COOKIE  --------------------------- //
\$config['cookie']['pre'] = '{cookiepre}';
\$config['cookie']['domain'] = '';
\$config['cookie']['path'] = '/';

// --------------------------  CONFIG SETTING  --------------------------- //
\$config['setting']['charset'] = 'utf-8';
\$config['setting']['cache'] = 'memcache';
\$config['setting']['timezone'] = 'Asia/Shanghai';
\$config['setting']['memory_limit'] = '256M';
\$config['setting']['filemode'] = 0644;
\$config['setting']['authkey'] = '{authkey}';
\$config['setting']['founder'] = '1';
\$config['setting']['development'] = 0;
\$config['setting']['referrer'] = 0;
\$config['setting']['https'] = 0;

// --------------------------  CONFIG UPLOAD  --------------------------- //
\$config['upload']['image']['extentions'] = array('gif', 'jpg', 'jpeg', 'png');
\$config['upload']['image']['limit'] = 5000;
\$config['upload']['attachdir'] = '{attachdir}';
\$config['upload']['audio']['extentions'] = array('mp3');
\$config['upload']['audio']['limit'] = 5000;

// --------------------------  CONFIG MEMCACHE  --------------------------- //
\$config['setting']['memcache']['server'] = '127.0.0.1';
\$config['setting']['memcache']['port'] = 11211;
\$config['setting']['memcache']['pconnect'] = 1;
\$config['setting']['memcache']['timeout'] = 30;
\$config['setting']['memcache']['session'] = 1;

// --------------------------  CONFIG PROXY  --------------------------- //
\$config['setting']['proxy']['host'] = '';
\$config['setting']['proxy']['auth'] = '';
EOF;
	return trim($cfg);
}

function local_mkdirs($path) {
	if(!is_dir($path)) {
		local_mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

function local_run($sql) {
	global $link, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", $sql);
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);
	foreach($ret as $query) {
		$query = trim($query);
		if($query) {
			if(!mysql_query($query, $link)) {
				echo mysql_errno() . ": " . mysql_error() . "<br />";
				exit($query);
			}
		}
	}
}

function local_create_sql($schema) {
	$pieces = explode('_', $schema['charset']);
	$charset = $pieces[0];
	$engine = $schema['engine'];
	$sql = "CREATE TABLE IF NOT EXISTS `{$schema['tablename']}` (\n";
	foreach ($schema['fields'] as $value) {
		if(!empty($value['length'])) {
			$length = "({$value['length']})";
		} else {
			$length = '';
		}

		$signed  = empty($value['signed']) ? ' unsigned' : '';
		if(empty($value['null'])) {
			$null = ' NOT NULL';
		} else {
			$null = '';
		}
		if(isset($value['default'])) {
			$default = " DEFAULT '" . $value['default'] . "'";
		} else {
			$default = '';
		}
		if($value['increment']) {
			$increment = ' AUTO_INCREMENT';
		} else {
			$increment = '';
		}

		$sql .= "`{$value['name']}` {$value['type']}{$length}{$signed}{$null}{$default}{$increment},\n";
	}
	foreach ($schema['indexes'] as $value) {
		$fields = implode('`,`', $value['fields']);
		if($value['type'] == 'index') {
			$sql .= "KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'unique') {
			$sql .= "UNIQUE KEY `{$value['name']}` (`{$fields}`),\n";
		}
		if($value['type'] == 'primary') {
			$sql .= "PRIMARY KEY (`{$fields}`),\n";
		}
	}
	$sql = rtrim($sql);
	$sql = rtrim($sql, ',');

	$sql .= "\n) ENGINE=$engine DEFAULT CHARSET=$charset;\n\n";
	return $sql;
}