<?php

class SAssociationProxy
{
    public static function getInstance($owner, $name, $options, $mapping = array())
    {
        if (!is_array($options))
        {
            $type = $options;
            $options = array();
            $options['type'] = $type;
        }
        
        list($assocType, $dest, $assocOptions) = self::getOptions($owner, $name, $options, $mapping);
        if ($options['type'] == 'to_many') self::registerToManyMethods($owner, $name, $dest);
        else self::registerToOneMethods($owner, $name, $dest);
        $assocClass = 'S'.ucfirst($assocType).'Association';
        return new $assocClass($owner, $name, $dest, $assocOptions);
    }
    
    public static function getOptions($owner, $name, $options, $mapping = array())
    {
        if (!isset($options['type'])) throw new SException('Type of relationship is required.');
        
        $type = $options['type'];
        
        if (!isset($options['dest']))
        {
            if ($type == 'to_many') $options['dest'] = SInflection::singularize($name);
            else $options['dest'] = $name;
        }
        
        $dest = strtolower($options['dest']);
        // we instanciate the dest class without associations to avoid an infinite loop
        if (!class_exists($dest))
            SDependencies::requireDependency('models', $dest, get_class($owner));
        
        $destInstance = new $dest(Null, True);
        
        $mapping['table_name']  = $destInstance->tableName;
        $mapping['primary_key'] = $destInstance->identityField;
        
        if (isset($options['inverse']) && $options['inverse'] == true) $inverse = true;
        else $inverse = false;
        
        if (!isset($mapping['assoc_type']))
            $assocType = self::findAssocType($type, $destInstance, get_class($owner), $inverse);
        else
            $assocType = $mapping['assoc_type'];
        
        return array($assocType, $dest, self::$assocType($owner, $name, $dest, $mapping));
    }
    
    public static function hasMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        
        return $options;
    }
    
    public static function belongsTo($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = $dest.'_id';
        
        return $options;
    }
    
    public static function manyToMany($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key', 'association_foreign_key', 'join_table'));
        if (!isset($options['foreign_key'])) 
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = $dest.'_id';
        if (!isset($options['join_table']))
            $options['join_table'] = self::joinTableName($owner->tableName, $options['table_name']);
        
        return $options;
    }
    
    public static function oneToOne($owner, $name, $dest, $options = array())
    {
        self::assertValidOptions($options, array('foreign_key'));
        if (!isset($options['foreign_key']))
            $options['foreign_key'] = strtolower(get_class($owner)).'_id';
        if (!isset($options['association_foreign_key'])) 
            $options['association_foreign_key'] = $dest.'_id';
        
        return $options;
    }
    
    private static function registerToOneMethods($owner, $name, $dest)
    {
        $owner->registerAssociationMethod($name, $name, 'read');
        $owner->registerAssociationMethod('build'.ucfirst($dest), $name, 'build');
        $owner->registerAssociationMethod('create'.ucfirst($dest), $name, 'create');
    }
    
    private static function registerToManyMethods($owner, $name, $dest)
    {
        $owner->registerAssociationMethod($name, $name, 'read');
        $owner->registerAssociationMethod('count'.ucfirst($name), $name, 'count');
        $owner->registerAssociationMethod('build'.ucfirst($name), $name, 'build');
        $owner->registerAssociationMethod('create'.ucfirst($name), $name, 'create');
        $owner->registerAssociationMethod('delete'.ucfirst($name), $name, 'delete');
        $owner->registerAssociationMethod('clear'.ucfirst($name), $name, 'clear');
    }
    
    private static function findAssocType($relationType, $destInstance, $ownerClass, $hasInverse = False)
    {
        if ($hasInverse && ($inverseType = self::findInverseType($destInstance, $ownerClass)) === false)
                throw new SException('Could not find inverse relationship.');
        
        if ($relationType == 'to_one')
        {
            if ($hasInverse && $inverseType == 'to_one') return 'oneToOne';
            return 'belongsTo';
        }
        elseif ($relationType == 'to_many')
        {
            if ($hasInverse && $inverseType == 'to_many') return 'manyToMany';
            return 'hasMany';
        }
    }
    
    private static function findInverseType($destInstance, $ownerClass)
    {
        $type = false;
        foreach ($destInstance->relationships as $relName => $relOptions)
        {
            if ((isset($relOptions['dest']) && $relOptions['dest'] == $ownerClass)
                || $relName == $ownerClass || SInflection::singularize($relName) == $ownerClass)
            {
                $type = $relOptions['type'];
                break;
            }
        }
        
        return $type;
    }
    
    private static function joinTableName($firstName, $secondName)
    {
        if ($firstName < $secondName)
            return "${firstName}_${secondName}";
        else
            return "${secondName}_${firstName}";
    }
    
    private static function assertValidOptions($options, $validOptions)
    {
        $validOptions = array_merge(array('table_name', 'primary_key', 'assoc_type'), $validOptions);
        foreach(array_keys($options) as $key)
        {
            if (!in_array($key, $validOptions))
                throw new SException($key.' is not a valid mapping option.');
        }
    }
}

?>