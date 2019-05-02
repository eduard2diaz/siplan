<?php
/**
 * Created by PhpStorm.
 * User: eduardo
 * Date: 29/04/19
 * Time: 15:54
 */

namespace App\Services;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Ldap\Ldap;

class LdapService
{
    private $ldap_dominio;
    private $ldap_server;
    private $ldap_version;
    private $ldap_dn;
    private $ldap_puerto;
    private $ldap_username;
    private $ldap_password;

    /**
     * LdapService constructor.
     * @param $ldap_dominio
     * @param $ldap_server
     * @param $ldap_version
     * @param $ldap_dn
     * @param $ldap_puerto
     * @param $ldap_username
     * @param $ldap_password
     */
    public function __construct($ldap_dominio, $ldap_server, $ldap_version, $ldap_dn, $ldap_puerto, $ldap_username, $ldap_password)
    {
        $this->ldap_dominio = $ldap_dominio;
        $this->ldap_server = $ldap_server;
        $this->ldap_version = $ldap_version;
        $this->ldap_dn = $ldap_dn;
        $this->ldap_puerto = $ldap_puerto;
        $this->ldap_username = $ldap_username;
        $this->ldap_password = $ldap_password;
    }


    public function login($username,$password){
        $dominio = $this->ldap_dominio;
        $dn = $this->ldap_dn;
        $puertoldap = $this->ldap_puerto;
        $ldaprdn = $username . '@' . $dominio;
        $ldappass = $password;
        $ldap = Ldap::create('ext_ldap', [
            'host' => $this->ldap_server,
            'port' => $puertoldap,
            'version' => $this->ldap_version,
        ]);
        $ldapconn = ldap_connect($dominio, $puertoldap);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        $ldapbind = @ldap_bind($ldapconn, $ldaprdn, $ldappass);
        if (!$ldapbind) {
            return false;
        }
        return true;
    }

    public function search($users){
        $dominio = $this->ldap_dominio;
        $dn = $this->ldap_dn;
        $puertoldap = $this->ldap_puerto;
        $ldaprdn = $this->ldap_username . '@' . $dominio;
        $ldappass = $this->ldap_password;
        $ldap = Ldap::create('ext_ldap', [
            'host' => $this->ldap_server,
            'port' => $puertoldap,
            'version' => $this->ldap_version,
        ]);
        $ldapconn = ldap_connect($dominio, $puertoldap);
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
        $ldapbind = @ldap_bind($ldapconn, $ldaprdn, $ldappass);
        if (!$ldapbind) {
            return false;
        }

        $person=$users;
        $filter="(|(sn=$person*)(sAMAccountName=$person*))";
        $sr=ldap_search($ldapconn,$dn,$filter);
        $info = ldap_get_entries($ldapconn, $sr);
        if($info['count']==0){
            $result=['error'=>0];
        }else
            if($info['count']==1){
                $nombre=$info[0]["displayname"][0];
                $usuario=$info[0]['samaccountname'][0];
                $correo=$info[0]['mail'][0];
                $result=['usuario'=>$usuario,'correo'=>$correo,'nombre'=>$nombre];
            }else
                $result=['error'=>2];

        return new JsonResponse($result);
    }
}