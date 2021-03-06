<?php

class SLocale
{
    public static $language = 'en_US';
    private static $strings = array();
    
    public static function initialize($detectLanguage = true)
    {
        if ($detectLanguage) self::detectLanguage();
        self::loadStrings(ROOT_DIR.'/core/common/lib/locale/');
        self::setLocale();
    }
    
    public static function setLocale()
    {
        putenv("LANG=".self::$language."");
        if (count($exp = explode('_', self::$language)) != 1) $winLanguage = $exp[0];
        else $winLanguage = self::$language;
        setlocale(LC_TIME, $winLanguage, self::$language, self::$language.'.UTF-8');
    }
    
    public static function translate($key)
    {
        if (is_array($key))
        {
            foreach ($key as $k => $v) $key[$k] = self::translate($v);
            return $key;
        }
        else
        {
            if (isset(self::$strings[$key])) return self::$strings[$key];
            else return $key;
        }
    }
    
    public static function loadStrings($dir)
    {
        $path = $dir.self::$language.'.php';
        
        if (file_exists($path))
            self::$strings = array_merge(self::$strings, include($path));
    }
    
    private static function detectLanguage()
    {
        foreach (self::getAcceptedLanguages() as $language)
        {
            if (file_exists(ROOT_DIR.'/core/common/lib/locale/'.$language.'.php'))
            {
                self::$language = $language;
                break;
            }
        }
    }
    
    private static function getAcceptedLanguages()
    {
        $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $qcandidat = 0;
        $nblang = count($langs);
        
        for ($i=0; $i<$nblang; $i++)
        {
            for ($j=0; $j<count($langs); $j++)
            {
                $lang = trim($langs[$j]); // Supprime les espaces avant et après $lang
                // Lang est de la forme langue;q=valeur
                
                if (!strstr($lang, ';') && $qcandidat != 1)
                {
                    // Si la chaine ne contient pas de valeur de préférence q
                    $candidat = $lang;
                    $qcandidat = 1;
                    $indicecandidat = $j;
                }
                else
                {
                    // On récupère l'indice q
                    $q = ereg_replace('.*;q=(.*)', '\\1', $lang);
                    
                    if ($q > $qcandidat)
                    {
                        $candidat = ereg_replace('(.*);.*', '\\1', $lang); ;
                        $qcandidat = $q;
                        $indicecandidat = $j;
                    } 
                }
            }
            
            if (strpos($candidat, '-'))
            {
                list($pref, $suff) = explode('-', $candidat);
                $candidat = $pref.'_'.strtoupper($suff);
            }
            
            $resultat[$i] = $candidat;
            
            $qcandidat=0;
            // On supprime la valeur du tableau
            unset($langs[$indicecandidat]);   
            $langs = array_values($langs);
        }
        return $resultat;
    }
}

?>
