<?php
namespace WScore\DataMapper;

class Role_Cenatar extends Role_Selectable
{
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Html\Selector             $selector
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Html\Selector
     */
    public function __construct( $em, $selector )
    {
        $this->em       = $em;
        $this->selector = $selector;
    }
    public function popHtml( $name, $html_type=null )
    {
        $html = parent::popHtml( $name, $html_type );
        if( $html instanceof \WScore\Html\Form ) {
            $cenaId = $this->entity->_get_cenaId();
            $format = $this->getFormName( $cenaId );
            $makeCena = function( $form ) use( $format ) {
                /** @var $tags \WScore\Html\Form */
                if( isset( $form->attributes[ 'name' ] ) ) {
                    $form->attributes[ 'name' ] = $format . '[' . $form->attributes[ 'name' ] . ']';
                }
            };
            $html->walk( $makeCena, 'name' );
        }
        return $html;
    }
    public function getFormName( $cenaId, $name=null ) {
        $cena = explode( '.', $cenaId );
        $formName = 'Cena[' . implode( '][', $cena ) . ']';
        if( $name ) $formName .= "[{$name}]";
        return $formName;
    }
}