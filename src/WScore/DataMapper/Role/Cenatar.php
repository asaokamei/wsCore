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
        $this->populateFormName( $html );
        return $html;
    }

    /**
     * @param \WScore\Html\Tags $html
     * @param string            $type
     * @return void
     */
    protected function populateFormName( $html, $type='prop' ) 
    {
        if( ! $html instanceof \WScore\Html\Form ) return;
        $cenaId = $this->entity->_get_cenaId();
        $format = $this->cena->getFormName( $cenaId, $type );
        $makeCena = function( $form ) use( $format ) {
            /** @var $tags \WScore\Html\Form */
            if( isset( $form->attributes[ 'name' ] ) ) {
                $name = $form->attributes[ 'name' ];
                $post = '';
                if( substr( $name, -2 ) == '[]' ) {
                    $name = substr( $name, 0, -2 );
                    $post = '[]';
                }
                $form->attributes[ 'name' ] = $format . '[' . $name . ']' . $post;
            }
        };
        $html->walk( $makeCena, 'name' );
    }

    /**
     * creates a hidden tag for a relation (HasOne or HasRefs).
     * 
     * @param string $name
     * @return \WScore\Html\Form
     */
    public function popLinkHidden( $name )
    {
        $targets  = $this->retrieve()->relation( $name );
        $hideDivs = $this->selector->form()->div();
        if( !empty( $targets ) ) {
            foreach( $targets as $target ) {
                $cenaId = $this->cena->cena . $this->cena->connector . $target->_get_cenaId();
                $tag = $this->selector->form()->input( 'hidden', $name, $cenaId )->multipleName();
                $this->populateFormName( $tag, 'link' );
                $hideDivs->contain_( $tag );
            }
        }
        return $hideDivs;
    }
    
    /**
     * creates a select box for a relation (many-to-many).
     * todo: refactor this method.
     *
     * @param string                               $name
     * @param \WScore\DataMapper\Entity_Collection $lists
     * @param string                               $display
     * @return \WScore\Html\Form|\WScore\Html\Tags
     */
    public function popLinkSelect( $name, $lists, $display )
    {
        $links = array();
        foreach( $lists as $entity ) {
            /** @var $entity Entity_Interface */
            $cenaId = $this->cena->cena . $this->cena->connector . $entity->_get_cenaId();
            $links[] = array( $cenaId, $entity[ $display ] );
        }
        $targets = $this->retrieve()->relation( $name );
        $selected = array();
        if( !empty( $targets ) )
            foreach( $targets as $tgt ) {
                $selected[] = $this->cena->cena . $this->cena->connector . $tgt->_get_cenaId();
            }
        $select = $this->selector->form()->select( 'groups', $links, $selected, array( 'multiple'=>true ) );
        $this->populateFormName( $select, 'link' );
        return $select;
    }
}