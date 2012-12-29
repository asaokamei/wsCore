<?php
namespace WScore\DataMapper;

class Role_Cenatar extends Role_Selectable
{
    protected $cena;
    // +----------------------------------------------------------------------+
    /**
     * @param \WScore\DataMapper\EntityManager    $em
     * @param \WScore\Html\Selector               $selector
     * @param \WScore\DataMapper\CenaManager      $cena
     * @DimInjection Get \WScore\DataMapper\EntityManager
     * @DimInjection Get \WScore\Html\Selector
     * @DimInjection Get \WScore\DataMapper\CenaManager
     */
    public function __construct( $em, $selector, $cena )
    {
        $this->em       = $em;
        $this->selector = $selector;
        $this->cena     = $cena;
    }
    /**
     * pops value of the $name (property name).
     * returns html-safe value if html_type is 'html',
     * returns html form element if html_type is 'form'.
     * 
     * Cenatar returns form with cena-formatted name such as
     *    name="Cena[model][get][id]"
     *
     * @param string $name
     * @param null   $html_type
     * @return mixed
     */
    public function popHtml( $name, $html_type=null )
    {
        $html = parent::popHtml( $name, $html_type );
        if( $html instanceof \WScore\Html\Form ) 
        {
            $cenaId = $this->entity->_get_cenaId();
            $format = $this->cena->getFormName( $cenaId );
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

}