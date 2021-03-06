<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

class RelationBuilder
{
    /** @var ConfigManager */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * @param ConfigInterface $sourceEntityConfig       The 'extend' config of the source entity
     * @param string          $targetEntityName
     * @param string          $relationName
     * @param string[]        $targetTitleFieldNames    Field names are used to show a title of related entity
     * @param string[]        $targetDetailedFieldNames Field names are used to show detailed info about related entity
     * @param string[]        $targetGridFieldNames     Field names are used to show related entity in a grid
     * @param array           $options
     * @param string          $fieldType                The field type. By default the field type is manyToMany,
     *                                                  but you can specify another type if it is based on manyToMany.
     *                                                  In this case this type should be registered
     *                                                  in entity_extend.yml under underlying_types section
     *
     * @return string The relation key
     */
    public function addManyToManyRelation(
        ConfigInterface $sourceEntityConfig,
        $targetEntityName,
        $relationName,
        array $targetTitleFieldNames,
        array $targetDetailedFieldNames,
        array $targetGridFieldNames,
        $options = [],
        $fieldType = 'manyToMany'
    ) {
        $sourceEntityName = $sourceEntityConfig->getId()->getClassName();
        $relationKey      = ExtendHelper::buildRelationKey(
            $sourceEntityName,
            $relationName,
            'manyToMany',
            $targetEntityName
        );

        // add a relation field config
        if (!$this->configManager->hasConfigFieldModel($sourceEntityName, $relationName)) {
            $this->configManager->createConfigFieldModel($sourceEntityName, $relationName, $fieldType);
            $options['extend']['state'] = ExtendScope::STATE_NEW;
        } else {
            $configFieldModel = $this->configManager->getConfigFieldModel($sourceEntityName, $relationName);
            if ($configFieldModel->getType() !== $fieldType) {
                $this->configManager->changeFieldType($sourceEntityName, $relationName, $fieldType);
            }
        }
        $options['extend']['is_extend']       = true;
        $options['extend']['relation_key']    = $relationKey;
        $options['extend']['target_entity']   = $targetEntityName;
        $options['extend']['target_title']    = $targetTitleFieldNames;
        $options['extend']['target_detailed'] = $targetDetailedFieldNames;
        $options['extend']['target_grid']     = $targetGridFieldNames;
        $this->updateFieldConfigs($sourceEntityName, $relationName, $options);

        // add relation to config
        $relations = $sourceEntityConfig->get('relation', false, []);
        if (!isset($relations[$relationKey])) {
            $relations[$relationKey] = [
                'assign'          => false,
                'field_id'        => new FieldConfigId('extend', $sourceEntityName, $relationName, 'manyToMany'),
                'owner'           => true,
                'target_entity'   => $targetEntityName,
                'target_field_id' => false,
            ];
            $sourceEntityConfig->set('relation', $relations);

            $extendConfigProvider = $this->configManager->getProvider('extend');
            $extendConfigProvider->persist($sourceEntityConfig);
        }

        return $relationKey;
    }

    /**
     * @param ConfigInterface $sourceEntityConfig The 'extend' config of the source entity
     * @param string          $targetEntityName
     * @param string          $relationName
     * @param string          $targetFieldName    A field name is used to show related entity
     * @param array           $options
     * @param string          $fieldType          The field type. By default the field type is manyToOne,
     *                                            but you can specify another type if it is based on manyToOne.
     *                                            In this case this type should be registered
     *                                            in entity_extend.yml under underlying_types section
     *
     * @return string The relation key
     */
    public function addManyToOneRelation(
        ConfigInterface $sourceEntityConfig,
        $targetEntityName,
        $relationName,
        $targetFieldName,
        $options = [],
        $fieldType = 'manyToOne'
    ) {
        $sourceEntityName = $sourceEntityConfig->getId()->getClassName();
        $relationKey      = ExtendHelper::buildRelationKey(
            $sourceEntityName,
            $relationName,
            'manyToOne',
            $targetEntityName
        );

        // add a relation field config
        if (!$this->configManager->hasConfigFieldModel($sourceEntityName, $relationName)) {
            $this->configManager->createConfigFieldModel($sourceEntityName, $relationName, $fieldType);
            $options['extend']['state'] = ExtendScope::STATE_NEW;
        } else {
            $configFieldModel = $this->configManager->getConfigFieldModel($sourceEntityName, $relationName);
            if ($configFieldModel->getType() !== $fieldType) {
                $this->configManager->changeFieldType($sourceEntityName, $relationName, $fieldType);
            }
        }
        $options['extend']['is_extend']     = true;
        $options['extend']['relation_key']  = $relationKey;
        $options['extend']['target_entity'] = $targetEntityName;
        $options['extend']['target_field']  = $targetFieldName;
        $this->updateFieldConfigs($sourceEntityName, $relationName, $options);

        // add relation to config
        $relations = $sourceEntityConfig->get('relation', false, []);
        if (!isset($relations[$relationKey])) {
            $relations[$relationKey] = [
                'assign'          => false,
                'field_id'        => new FieldConfigId('extend', $sourceEntityName, $relationName, 'manyToOne'),
                'owner'           => true,
                'target_entity'   => $targetEntityName,
                'target_field_id' => false
            ];
            $sourceEntityConfig->set('relation', $relations);

            $extendConfigProvider = $this->configManager->getProvider('extend');
            $extendConfigProvider->persist($sourceEntityConfig);
        }

        return $relationKey;
    }

    /**
     * @param string $targetEntityName
     * @param string $sourceEntityName
     * @param string $relationName
     * @param string $relationKey
     */
    public function addManyToOneRelationTargetSide(
        $targetEntityName,
        $sourceEntityName,
        $relationName,
        $relationKey
    ) {
        $extendConfigProvider = $this->configManager->getProvider('extend');
        $extendConfig         = $extendConfigProvider->getConfig($targetEntityName);

        // add relation to config
        $relations               = $extendConfig->get('relation', false, []);
        $relations[$relationKey] = [
            'assign'          => false,
            'field_id'        => false,
            'owner'           => false,
            'target_entity'   => $sourceEntityName,
            'target_field_id' => new FieldConfigId('extend', $sourceEntityName, $relationName, 'manyToOne')
        ];
        $extendConfig->set('relation', $relations);

        $extendConfigProvider->persist($extendConfig);
    }

    /**
     * @param string $className
     * @param array  $options
     */
    public function updateEntityConfigs($className, $options)
    {
        $this->updateConfigs($className, $options);
    }

    /**
     * @param string $className
     * @param string $fieldName
     * @param array  $options
     */
    public function updateFieldConfigs($className, $fieldName, $options)
    {
        $this->updateConfigs($className, $options, $fieldName);
    }

    /**
     * @param string $className
     * @param array  $options
     * @param string $fieldName
     */
    protected function updateConfigs($className, $options, $fieldName = null)
    {
        foreach ($options as $scope => $scopeValues) {
            $configProvider = $this->configManager->getProvider($scope);
            $config         = $configProvider->getConfig($className, $fieldName);
            $hasChanges     = false;
            foreach ($scopeValues as $code => $val) {
                if (!$config->is($code, $val)) {
                    $config->set($code, $val);
                    $hasChanges = true;
                }
            }
            if ($hasChanges) {
                $configProvider->persist($config);
            }
        }
    }
}
