<?php

namespace Syncrasy\PimcoreSalesforceBundle\Controller;

use Exception;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Tool\Admin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Syncrasy\PimcoreSalesforceBundle\Model\Mapping;
use Syncrasy\PimcoreSalesforceBundle\Services\MappingService;

/**
 * @package PimcoreSalesforceBundle\Controller
 * @Route("/admin/pimcoresalesforce/mapping")
 */
class MappingController extends AdminController

{
    protected const SUCCESS = 'success';
    protected const ERROR = 'error';
    protected const LIMIT = 'limit';
    protected const CLASS_NAME = 'Mapping';
    protected const MESSAGE = 'message';

    /**
     * add mapping
     * @Route("/tree")
     * @param Request $request
     * @return JsonResponse
     */
    public function getMappingTreeAction(Request $request)
    {
        $obj = new Mapping\Listing();
        $mapping = $obj->load();
        $mappings = [];
        foreach ($mapping as $row) {
            $mappings[] = $this->buildItem($row);
        }
        return $this->json(['nodes' => $mappings]);
    }

    /**
     * add mapping
     * @Route("/add")
     * @param Request $request
     * @return JsonResponse
     */
    public function mappingTreeAddAction(Request $request)
    {
        try {
            $name = $request->get('name');
            $object = Mapping::getByName(trim($name));
            if (!$object instanceof Mapping) {
                $newObject = new Mapping();
                $newObject->setName($name);
                $mappingAttributes = $this->encodeJson(MappingService::getMappingInfo([]));
                $newObject->setLanguage(Admin::getCurrentUser()->getLanguage());
                $newObject->setColumnAttributeMapping($mappingAttributes);
                $userOwner = (int)Admin::getCurrentUser()->getId();
                $newObject->setUserOwner($userOwner);
                $newObject->save();
                return $this->json([self::SUCCESS => true, "id" => $newObject->getId(), 'message' => '']);
            } else {
                $message = 'prevented creating object because object with same path+key already exists';
                return $this->json([self::SUCCESS => false, self::MESSAGE => $message, 'id' => $object->getId()]);
            }
        } catch (Exception $ex) {
            return $this->json([self::SUCCESS => false, self::MESSAGE => $ex->getMessage()]);
        }
    }

    /**
     * add mapping
     * @Route("/get")
     * @param Request $request
     * @return JsonResponse
     */

    public function mappingGetAction(Request $request)
    {

        try {
            $id = $request->get('id');
            $object = Mapping::getById(trim($id));
            $data[] = $this->buildItem($object);
            $data['lang'] = !empty($object->getLanguage()) ? $object->getLanguage() : Admin::getCurrentUser()->getLanguage();
            $data['columnAttributeMapping'] = json_decode(json_encode($object->getColumnAttributeMapping()), true);

            return $this->json(['result' => true, 'general' => ['o_id' => $object->getId(), 'o_key' => $object->getName()], 'data' => $data, "msg" => '']);
        } catch (Exception $ex) {
            return $this->json([self::SUCCESS => false, self::MESSAGE => $ex->getMessage()]);
        }
    }

    /**
     * @param Mapping $mapping
     *
     * @return array
     */
    private function buildItem($mapping): array
    {
        return [
            'id' => $mapping->getId(),
            'text' => $mapping->getName(),
            'key' => $mapping->getName(),
            'pimcoreClassId' => $mapping->getPimcoreClassId(),
            'pimcoreUniqueField' => $mapping->getPimcoreUniqueField(),
            'salesforceObject'=> $mapping->getSalesforceObject(),
            'salesforceUniqueField' => $mapping->getSalesforceUniqueField(),
            'fieldForSfId' => $mapping->getFieldForSfId()
        ];
    }
}