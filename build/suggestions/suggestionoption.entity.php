<?php

/**
 * represents a single decision
 *
 */
class SuggestionOption extends RoxEntityBase
{
    protected $_table_name = 'suggestions_options';

    public function __construct($optionId = false)
    {
        parent::__construct();
        if ($optionId)
        {
            $this->findById($optionId);
        }
    }

    /**
     * overloads RoxEntityBase::loadEntity to load related data
     *
     * @param array $data
     *
     * @access protected
     * @return bool
     */
    protected function loadEntity(array $data)
    {
        if ($status = parent::loadEntity($data))
        {
            $entityFactory = new RoxEntityFactory();
            $this->creator = $entityFactory->create('Member', $this->createdBy);
                    if ($this->modifiedBy) {
                $this->modifier = $entityFactory->create('Member', $this->modifiedBy);
            }
            if ($this->deletedBy) {
                $this->deleter = $entityFactory->create('Member', $this->deletedBy);
            }
            if ($this->mutuallyExclusiveWith) {
                $this->mutuallyExclusive = explode(',',$this->mutuallyExclusiveWith);
            }
        }
        return $status;
    }
}