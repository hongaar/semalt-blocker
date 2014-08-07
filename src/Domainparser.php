<?php
namespace Nabble;

/**
 * Class Domainparser
 * @package Nabble
 * @see http://forums.devshed.com/php-development-5/root-domain-url-551863.html
 */
class Domainparser
{
    public static function parseUrl($url)
    {
        $element = array ( 'url', 'scheme', 'user', 'pass', 'domain', 'port', 'path', 'query', 'fragment' );

        $r  = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?';
        $r .= '(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';

        preg_match_all ( $r, $url, $out );

        $return = array ();

        foreach ( $element AS $n => $v )
        {
            $return[$v] = $out[$n][0];
        }

        $return['topleveldomain'] = $return['subdomain'] = $return['toplevelname'] = '';

        $return = empty($return['domain']) ? $return : static::getDomain($return);
        if (empty($return['topleveldomain'])) {
            $return['topleveldomain'] = $return['domain'];
        }
        return $return;
    }

    private static function getDomain ($host)
    {
        if ( ( $total_parts = substr_count ( $host['domain'], '.' ) ) <= 1 )
        {
            return $host;
        }

        $parts_array = explode ( '.', $host['domain'] );

        $last_part = $parts_array[$total_parts];

        $test_part = $parts_array[--$total_parts] . '.' . $last_part;

        $top_names = 'ac.cn,ac.jp,ac.uk,ad.jp,adm.br,adv.br,agr.br,ah.cn,am.br,arq.br,art.br,asn.au,ato.br,av.tr,bel.tr,bio.br,biz.tr,bj.cn,bmd.br,cim.br,cng.br,cnt.br,co.at,co.jp,co.uk,com.au,com.br,com.cn,com.eg,com.hk,com.mx,com.ru,com.tr,com.tw,conf.au,cq.cn,csiro.au,dr.tr,ecn.br,edu.au,edu.br,edu.tr,emu.id.au,eng.br,esp.br,etc.br,eti.br,eun.eg,far.br,fj.cn,fm.br,fnd.br,fot.br,fst.br,g12.br,gb.com,gb.net,gd.cn,gen.tr,ggf.br,gob.mx,gov.au,gov.br,gov.cn,gov.hk,gov.tr,gr.jp,gs.cn,gx.cn,gz.cn,ha.cn,hb.cn,he.cn,hi.cn,hk.cn,hl.cn,hn.cn,id.au,idv.tw,imb.br,ind.br,inf.br,info.au,info.tr,jl.cn,jor.br,js.cn,jx.cn,k12.tr,lel.br,ln.cn,ltd.uk,mat.br,me.uk,med.br,mil.br,mil.tr,mo.cn,mus.br,name.tr,ne.jp,net.au,net.br,net.cn,net.eg,net.hk,net.lu,net.mx,net.ru,net.tr,net.tw,net.uk,nm.cn,no.com,nom.br,not.br,ntr.br,nx.cn,odo.br,oop.br,or.at,or.jp,org.au,org.br,org.cn,org.hk,org.lu,org.ru,org.tr,org.tw,org.uk,plc.uk,pol.tr,pp.ru,ppg.br,pro.br,psc.br,psi.br,qh.cn,qsl.br,rec.br,sc.cn,sd.cn,se.com,se.net,sh.cn,slg.br,sn.cn,srv.br,sx.cn,tel.tr,tj.cn,tmp.br,trd.br,tur.br,tv.br,tw.cn,uk.com,uk.net,vet.br,wattle.id.au,web.tr,xj.cn,xz.cn,yn.cn,zj.cn,zlg.br,co.nr,co.nz,com.fr,';

        if ( strpos ( $top_names, $test_part . ',' ) )
        {
            $last_part = $parts_array[--$total_parts] . '.' . $test_part;

            if ( strpos ( $top_names, $last_part . ',' ) )
            {
                $host['toplevelname']   = $last_part;
                $last_part              = $parts_array[--$total_parts] . '.' . $last_part;
                $host['topleveldomain'] = $last_part;
                $host['subdomain']      = str_ireplace ( '.' . $last_part, '', $host['domain'] );
            }
            else
            {
                $host['topleveldomain'] = $last_part;
                $host['subdomain']      = str_ireplace ( '.' . $last_part, '', $host['domain'] );
                $host['toplevelname']   = $test_part;
            }
        }
        else
        {
            $host['topleveldomain'] = $test_part;
            $host['subdomain']      = str_ireplace ( '.' . $test_part, '', $host['domain'] );
            $host['toplevelname']   = $last_part;
        }

        return $host;
    }
}