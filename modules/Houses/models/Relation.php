<?php


class Houses_Relation_Model extends Vtiger_Relation_Model
{

    public function addRelation($sourcerecordId, $destinationRecordId)
    {

        parent::addRelation($sourcerecordId, $destinationRecordId);

        $destinationModuleName = $this->getRelationModuleModel()->get('name');

        if ($destinationModuleName == 'Services')
        {

            $sourceModuleName = 'Flats';
            $focus = CRMEntity::getInstance($sourceModuleName);

            $flats = $this->getFlats($sourcerecordId);

            foreach ($flats as $id)
            {
                relateEntities($focus, $sourceModuleName, $id, $destinationModuleName, $destinationRecordId);
            }
        }
    }


    public function deleteRelation($sourceRecordId, $relatedRecordId)
    {

        parent::deleteRelation($sourceRecordId, $relatedRecordId);

        $destinationModuleName = $this->getRelationModuleModel()->get('name');

        if ($destinationModuleName == 'Services')
        {

            $destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
            $sourceModuleName = 'Flats';
            $flats = $this->getFlats($sourceRecordId);

            foreach ($flats as $id)
            {
                DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $id);
            }
        }

        return true;
    }


    /*
     * return array of flats id by house id
     *
     */

    private function getFlats($id)
    {

        global $adb;

        $response = array();

        $sql =
        "
            select fcf.flatsid 
            from vtiger_flatscf fcf 
            join vtiger_crmentity ce on ce.crmid = fcf.flatsid 
            where ce.deleted = 0 and fcf.cf_1203 = $id
        ";

        $result = $adb->query($sql);

        $n = $adb->num_rows($result);

        for ($i = 0; $i < $n; $i++)
        {
            $flatId = $adb->query_result($result, $i, 0);
            $response[] = $flatId;
        }

        return $response;
    }
}
