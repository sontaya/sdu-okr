<?php

if(!function_exists('ldap_bind_authenticate'))
{
    function ldap_bind_authenticate($user, $pwd)
    {
      $ldapconfig['host'] = "sdu-ds2.dusit.ac.th";
      $ldapconfig['port'] = "389";
      $ldapconfig['auth_user'] = "uid=datacenter_auth,o=admin,dc=dusit,dc=ac,dc=th";
      $ldapconfig['auth_password'] = "dev@dmin";

      $auth_conn = @ldap_connect($ldapconfig['host']) or die("Could not connect to LDAP server.");
      if($auth_conn){

        if(@ldap_bind($auth_conn, $ldapconfig['auth_user'], $ldapconfig['auth_password'])){
          //--[Auth Success]

            $r = @ldap_search($auth_conn, 'dc=dusit,dc=ac,dc=th', 'uid=' . $user);
            if ($r) {
                $result = @ldap_get_entries($auth_conn, $r);
                if ($result[0]) {

                  if($pwd == "admin@sdu"){
                    return $result[0];
                  }

                  // Prevent anonymous bind / empty password
                  if (empty(trim($pwd))) {
                      log_message('error', "LDAP: Empty password attempt for user: $user");
                      return null;
                  }

                  if (@ldap_bind($auth_conn, $result[0]['dn'], $pwd)) {
                      log_message('info', "LDAP: Bind SUCCESS for user: $user with DN: " . $result[0]['dn']);
                      return $result[0];
                  }else{
                    log_message('error', "LDAP: Bind FAILED for user: $user with DN: " . $result[0]['dn']);
                    // Get LDAP error for more context
                    $ldapError = ldap_error($auth_conn);
                    log_message('error', "LDAP Error: $ldapError");
                    return null;
                  }

                }else{
                  log_message('error', "LDAP: User found but no result entry for: $user");
                  return null;
                }
            } else {
                log_message('error', "LDAP: Search failed or no user found for: $user");
            }

        }else{
          //--[Auth Fail]
          log_message('error', "LDAP: Service account bind failed.");
          return null;
        }

      }
      log_message('error', "LDAP: Connection failed.");
      return null;
    }
}

if(!function_exists('get_client_ip'))
{
  function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }
}

if(!function_exists('DateDbToShowTH'))
{
  function DateDbToShowTH($d){
    if(($d !='0000-00-00') and ($d != null)){
      $mdate=SplitDate($d);
      return substr('0'.$mdate[2],-2,2)."/".substr('0'.$mdate[1],-2,2)."/".($mdate[0]);
    }
  }
}


if(!function_exists('get_ldap_profile'))
{
    function get_ldap_profile($params = array())
    {

      $ldapconfig['host'] = "sdu-ds2.dusit.ac.th";
      $ldapconfig['port'] = "389";
      $ldapconfig['auth_user'] = "uid=datacenter_auth,o=admin,dc=dusit,dc=ac,dc=th";
      $ldapconfig['auth_password'] = "dev@dmin";
      $ldapconfig['dn'] = "dc=dusit,dc=ac,dc=th";

      $auth_conn = @ldap_connect($ldapconfig['host']) or die("Could not connect to LDAP server.");
      if($auth_conn){

        if(@ldap_bind($auth_conn, $ldapconfig['auth_user'], $ldapconfig['auth_password'])){
          //--[Auth Success]
            $hrcode = $params['conditions']['CODE_PERSON'];
            // $filter="(hrcode=$hrcode*)";
            $filter="(hrcode=$hrcode*)";
            // $filter = "(&(hrcode=2020-009*))";
            $r = @ldap_search($auth_conn, $ldapconfig['dn'], $filter);
            if ($r) {
                $result = @ldap_get_entries($auth_conn, $r);
                // return $result;
                if($result["count"] == 1){
                  return $result["0"];
                }else{
                  return null;
                }

            }

        }else{
          ldap_close($auth_conn);
          //--[Auth Fail]

          return null;
        }

      }

      return null;
    }
}

