<?php
# ***** BEGIN LICENSE BLOCK *****
# Version: MPL 1.1/GPL 2.0/LGPL 2.1
#
# The contents of this file are subject to the Mozilla Public License Version
# 1.1 (the "License"); you may not use this file except in compliance with
# the License. You may obtain a copy of the License at
# http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS IS" basis,
# WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
# for the specific language governing rights and limitations under the
# License.
#
# The Original Code is Weave Basic Object Server
#
# The Initial Developer of the Original Code is
# Mozilla Labs.
# Portions created by the Initial Developer are Copyright (C) 2008
# the Initial Developer. All Rights Reserved.
#
# Contributor(s):
#	Toby Elliott (telliott@mozilla.com)
#
# Alternatively, the contents of this file may be used under the terms of
# either the GNU General Public License Version 2 or later (the "GPL"), or
# the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
# in which case the provisions of the GPL or the LGPL are applicable instead
# of those above. If you wish to allow use of your version of this file only
# under the terms of either the GPL or the LGPL, and not to allow others to
# use your version of this file under the terms of the MPL, indicate your
# decision by deleting the provisions above and replace them with the notice
# and other provisions required by the GPL or the LGPL. If you do not delete
# the provisions above, a recipient may use your version of this file under
# the terms of any one of the MPL, the GPL or the LGPL.
#
# ***** END LICENSE BLOCK *****

	#get and parse the config file
	if ($argc < 2)
		exit("Please provide an input file");
	
	$input = fopen($argv[1], 'r');
	if (!$input)
		echo "cannot open config file";
	
	$json = '';	
	while ($data = fread($input,2048)) {$json .= $data;}
	$config = json_decode($json, true);
	
	if (!$config)
		exit("cannot read config json");
	
	
	#Connect to the account db
	try
	{
		$dbh = new PDO('mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['db'], 
						$config['database']['username'], $config['database']['password']);
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch( PDOException $exception )
	{
		echo "Database unavailable: " . $exception->getMessage();
		exit;
	}

	foreach ($config['nodes'] as $node)
	{
		try
		{
			$insert_stmt = 'replace into available_nodes (node, ct) values (?,?)';
			$sth = $dbh->prepare($insert_stmt);
			$sth->execute(array($node, $config['accounts_per_node']));
		}
		catch( PDOException $exception )
		{
			error_log("refresh_accounts: " . $exception->getMessage());
			throw new Exception("Database unavailable", 503);
		}
	}


?>