<?php

namespace Smarch\Amazo\Models;

use Illuminate\Database\Eloquent\Model;

use Smarch\Amazo\Models\AmazoMods;

class Amazo extends Model
{
    /**
     * Constants
     */
    const ATTR_NAME = 'name';
    const ATTR_SLUG = 'slug';
    const ATTR_NOTES = 'notes';
    const ATTR_ENABLED = 'enabled';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'damage_types';


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        self::ATTR_NAME,
        self::ATTR_SLUG,
        self::ATTR_NOTES,
        self::ATTR_ENABLED
    ];
    

    /**
     * Get the mods for the damage type
     * 
     * @return object
     */
    public function modifiers()
    {
        return $this->hasMany('Smarch\Amazo\Models\AmazoMods', 'parent_id');
    }

    public function getDamage($damage = 0)
    {
        $mods = $this->modifiers;

        $object = new \stdClass();
        $object->startingDamage = $damage;
        $object->addedModifierDamage = 0;

        foreach($mods as $item) {
            $bcOperator = ($item->mod_type === "+") ? 'bcadd' : 'bcmul';
            $modDamage = call_user_func($bcOperator, $object->startingDamage, $item->amount);

            $props[] = (object) [ 
                'message' => $item->damageType->name . " generated " . $modDamage . " damage (".$damage . " ".$item->mod_type." ".$item->amount.")",
                'parentName' => $this->getName(),
                'modifierName' => $item->damageType->name,
                'modifierAmount' => $item->amount,
                'modifierDamage' => $modDamage,
                'operator' => (object) [ 
                    'stringOperator' => $item->mod_type,
                    'bcOperator' => $bcOperator
                ]
            ];

            $object->addedModifierDamage += $modDamage;
        }

        $object->totalDamage = ($object->startingDamage + $object->addedModifierDamage);
        
        $object->modifiers = (object) $props;

        return $object;
    }


    /**
     * Get Damage Type Name
     * @return string
     */
    public function getName()
    {
        return $this->{self::ATTR_NAME};
    }

    
    /**
     * Get Damage Type Slug
     * @return string
     */
    public function getSlug()
    {
        return $this->{self::ATTR_SLUG};
    }

}
