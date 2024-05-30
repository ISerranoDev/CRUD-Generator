<?php
/**
 * Servicio para controlar y gestionar los filtros y orden de un listado
 *
 */

namespace ISerranoDev\CrudGenerator\Utils\Tools;

use ISerranoDev\CrudGenerator\Utils\Validator;
use Symfony\Component\HttpFoundation\Request;

class FilterService
{
    public array $order = [];
    public array $filters = [];
    public int $page = 1;
    public int $limit = 25;

    /**
     * Esta propiedad solo se rellenará en el caso de que el sistema de orden esté contenido en una variable única llamada current_request,
     * dentro de la petición que se realiza en el momento.
     * @var string
     *
     *
     */
    public $currentRequest = null;

    private $stringPath = "";
    private Request $request;

public function __construct(FilterRequest $request)
    {

        $this->order = @$request->getAttribute('filter_order') ?: [];
        $this->currentRequest = @$request->getAttribute('current_request') ?: null;
        $this->filters = @$request->getAttribute('filter_filters') ?: [];
        $this->page = @$request->getAttribute('page') ?: 1;
        $this->limit = @$request->getAttribute('limit') ?: 25;
        $this->request = $request->getRequest();

        $pathPaterns = explode("?", $this->request->getUri());

        $this->stringPath = $pathPaterns[0];

    }


    /**
     * Genera el link para cambiar un orden del filtro.
     * @param $field
     * @param $order
     * @return string
     */
    public function orderBy($field, $order)
    {
        $completeRequest = $this->futureOrderRequest($field, $order);
        // Simulamos como quedaría el array después de añadirle el orden
        return $this->getCompletePath($completeRequest);
    }

    /**
     * recibe un array con los parámetros que hay que generar la query del enlace
     * @param $completeRequest
     * @return string
     */
    public function getCompletePath($completeRequest)
    {
        return $this->getPath() . "?" . $this->buildQuery($completeRequest);
    }

    /**
     * @return mixed
     */
    private function getPath()
    {
        return $this->stringPath;
    }

    /**
     * Funcion que hace un stringify de los parámetros
     * para devolver un string con la query
     * @param $param
     * @return string
     */
    public function buildQuery($param)
    {
        return http_build_query($param);
    }

    public function limitBy($newLimit){
        $completeRequest = $this->getCurrentRequestParams();
        $completeRequest["limit"] = $newLimit < 0 ? 50 : $newLimit;
        $completeRequest["page"] = 1;
        return $this->getCompletePath($completeRequest);
    }


    /**
     * Devuelve el array con los parámtros de la query actual.
     * @return array
    */
    public function getAll(): array
    {
        return [
            "filter_order" => $this->order,
            "filter_filters" => $this->filters,
            "limit" => $this->limit,
            "page" => $this->page,
        ];
    }

    public function getAllOrders()
    {
        return $this->order;
    }

    private function addOrder($field, $order, &$currentRequest)
    {
        $exist = false;
        foreach ($currentRequest["filter_order"] as $index => $orderField) {
            if ($orderField["field"] == $field) {
                $exist = true;
                $currentRequest["filter_order"][$index]["order"] = $order;
            }
        }
        if (!$exist) {
            $currentRequest["filter_order"] = [];

            $currentRequest["filter_order"][] = [
                "field" => $field,
                "order" => $order
            ];
        }
    }

    public function addOrderValue($field, $order)
    {
        $this->order = [["field" => $field, 'order' => $order]];
    }

    public function getCurrentRequestParams(){
        return [
            "filter_order" => $this->order,
            "filter_filters" => $this->filters,
            "limit" => $this->limit,
            "page" => $this->page,
        ];
    }

    public function addFilter($filterName, $value) {
        $this->filters[$filterName] =  $value;
        return $this;
    }


    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * Recupera un order específico si existe en los ordenes establecidos
     * @param $fieldName
     * @return bool|array
     */
    public function getOrder($fieldName)
    {
        foreach ($this->order as $orderField) {
            if ($orderField["field"] == $fieldName) {
                return $orderField;
            }
        }
        return false;
    }


    /**
     * Comprueba si en la request existe este campo ordenado.
     * @param $fieldName
     * @return bool
     */
    public function isOrdered($fieldName)
    {
        $parameters = $this->getAll();
        if ($parameters and isset($parameters['filter_order']) and count($parameters['filter_order']) > 0) {
            foreach ($parameters['filter_order'] as $order) {
                if ($order["field"] == $fieldName) return true;
            }
        }
        return false;
    }

    /**
     * Comprueba si ya existe un orden para ese campo y retorna el orden contrario,
     * Se puede utilizar para mostrar los iconos ordenar en una dirección y otra.
     */
    public function getInversedOrder($fieldName)
    {
        $order = $this->getOrder($fieldName);
        if ($order) {
            if ($order["order"] == "desc") return "asc";
            if ($order["order"] == "asc") return "desc";
        }
        return "asc";
    }

    public function getFilters(){
        return $this->filters;
    }

    public function getOrders(){
        return $this->order;
    }


    /**
     * Coge los parámetros actuales de la petición y modifica el orden para
     * que se pueda generar un link con el orden cambiado
     * @param $fieldName
     * @param $order
     * @return array
     */
    public function futureOrderRequest($fieldName, $order){
        $currentRequest = $this->getCurrentRequestParams();
        $this->addOrder($fieldName,$order, $currentRequest);
        return $currentRequest;
    }

    public function filterField($fieldName){
        return "filter_filters[$fieldName]";
    }

    public function filterFormField(){
        return '<input type="hidden" name="current_request" value="'.$this->getCurrentRequest().'">';
    }


    /**
     * Recupera el valor de un campo filtrado
     * @param $fieldName
     * @return mixed
     */
    public function getFilterValue($fieldName){
        foreach ($this->filters as $indexName => $filter) {
            if($indexName == $fieldName) {
                return $filter;
            }
        }
        return null;
    }

    public function pageBy($page){
        $completeRequest = $this->getCurrentRequestParams();
        $completeRequest["page"] = $page < 0 ? 1 : $page;
        return $this->getCompletePath($completeRequest);
    }

}