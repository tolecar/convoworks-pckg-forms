<?php

namespace Convo\Pckg\Forms;

use Convo\Core\Workflow\IConversationElement;
use Convo\Core\Workflow\IConvoRequest;
use Convo\Core\Workflow\IConvoResponse;
use Convo\Core\Params\IServiceParamsScope;

class SearchEntriesElement extends AbstractFormsElement
{
    private $_search=[];
    private $_resultVar;

    /**
     * @var IConversationElement[]
     */
    private $_multipleFlow = array();

    /**
     * @var IConversationElement[]
     */
    private $_singleFlow = array();

    /**
     * @var IConversationElement[]
     */
    private $_emptyFlow = array();

    /**
     * @param array $properties
     */
    public function __construct( $properties)
    {
        parent::__construct( $properties);

        $this->_search   	=   $properties['search'];
        $this->_resultVar   =   $properties['result_var'];

        foreach ( $properties['multiple_flow'] as $element) {
            $this->_multipleFlow[] = $element;
            $this->addChild($element);
        }

        foreach ( $properties['single_flow'] as $element) {
            $this->_singleFlow[] = $element;
            $this->addChild($element);
        }

        foreach ( $properties['empty_flow'] as $element) {
            $this->_emptyFlow[] = $element;
            $this->addChild($element);
        }
    }


    public function read( IConvoRequest $request, IConvoResponse $response)
    {
        $context   =   $this->_getFormsContext();
        $data      =   [];
        $params    =   $this->getService()->getComponentParams( IServiceParamsScope::SCOPE_TYPE_REQUEST, $this);

        $search     =   $this->_evaluateArgs( $this->_search);
        $result     =   $context->searchEntries( $search);
        
        $this->_logger->info( 'Found ['.count( $result).'] entries');
        $this->_logger->debug( 'Got result ['.print_r( $result, true).']');
        
        $data['result'] = $result;
        $data['count'] = count( $result);

        $elements = $this->_multipleFlow;

        if ( empty( $result)) {
            $elements = $this->_emptyFlow;
        } else if ( count( $result) === 1) {
            $elements = $this->_singleFlow;
        }
        
        $params->setServiceParam( $this->_resultVar, $data);
        
        foreach ( $elements as $elem) {
            $elem->read( $request, $response);
        }
    }

    // UTIL
    public function __toString()
    {
        return parent::__toString();
    }


}

