<?php

namespace Tokenizer;

class ArrayNS extends TokenAuto {
    static public $operators = array('T_OPEN_BRACKET');
    
    function _check() {
        $this->conditions = array(//-2 => array('filterOut' => array('T_DOUBLE_COLON', 'T_OBJECT_OPERATOR')), 
                                  //-1 => array('atom' => array('Variable', 'Property', 'Staticproperty', 'Array')),
                                   0 => array('token' => ArrayNS::$operators),
                                   1 => array('atom'  => 'yes', 'notAtom' => 'Arguments'),
                                   2 => array('token' => 'T_CLOSE_BRACKET'),
        );
        
        $this->actions = array('insertEdge'  => array(0 => array('Arguments' => 'ARGUMENTS')),
                               'keepIndexed' => true);
        $this->checkAuto();

        $this->conditions = array(//-2 => array('filterOut' => array('T_DOUBLE_COLON', 'T_OBJECT_OPERATOR')), 
                                  //-1 => array('atom' => array('Variable', 'Property', 'Staticproperty', 'Array')),
                                   0 => array('token' => ArrayNS::$operators),
                                   1 => array('token' => 'T_CLOSE_BRACKET'),
        );
        
        $this->actions = array('addEdge'     => array(0 => array('Void' => 'ARGUMENTS')),
                               'keepIndexed' => true,
                               'cleanIndex' => true);
        $this->checkAuto();

        $this->conditions = array(//-2 => array('filterOut' => array('T_DOUBLE_COLON', 'T_OBJECT_OPERATOR')), 
                                  //-1 => array('atom' => array('Variable', 'Property', 'Staticproperty', 'Array')),
                                   0 => array('token' => ArrayNS::$operators),
                                   1 => array('atom'  => 'Arguments'),
                                   2 => array('token' => 'T_CLOSE_BRACKET'),
        );
        
        $this->actions = array('transform'  => array( 1 => 'ARGUMENTS',
                                                      2 => 'DROP'),
                               'atom'       => 'ArrayNS',
                               'cleanIndex' => true);
        $this->checkAuto();

        return $this->checkRemaining();
    }
}

?>