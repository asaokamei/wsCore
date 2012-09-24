<?php

/*
 * Aspect Oriented Programming...
 *
 * After all, AOP is like inheritance of a class.
 * you intercept an original method to another method.
 * AOP is just a lot more freedom...
 *
 * Aspect Interceptor...
 *  1. wraps an object and intercepts a call.
 *  2. if the call has advice for it,
 *  3. invoke the advice object for the call.
 *  4. the advice object maybe invoke the original call.
 *
 * Need three classes.
 *  1. interceptor: wraps an object, and intercepts a call.
 *  2. container:   invoke advice for the intercepted call.
 *  3. adviser:     do advice.
 */

/*
 * memo (2012/09/24)
 *
 * I thought about this a while ago;
 * do not need before/after/catch type as long as around type
 * is available, and methods chain is implemented.
 *
 * memo (2012/09/24)
 *
 * using interface of a class will affect all the inherited class,
 * and that maybe too strongly coupled.
 *
 * using annotation for methods maybe too costly.
 *
 * so, use annotation for class to AOP independent class/methods,
 * such as:
 *
 *     @DimAspect   Adviser   methods1, methods2, ...
 *
 * this could be an acceptable, except that aspect for methods
 * are described at the top of the class. which is not obvious.
 *
 */
interface InjectAopContainerInterface {}

interface AopAdviserInterface {
    public function invoke( $joinPoint, $args, $invoke, $returned );
}

class AopInterceptor implements InjectAopContainerInterface
{
    /** @var string       */
    private $name = NULL;
    /** @var object       */
    private $obj  = NULL;
    /** @var AopContainer */
    private $container = NULL;

    /**
     * @param $obj       object to be intercepted.
     */
    public function __construct( $obj ) {
        $this->obj  = $obj;
        $this->name = get_class( $obj );
    }

    /**
     * @param $container AopContainer
     */
    public function injectAopContainer( $container ) {
        $this->container = $container;
    }

    /**
     * intercepts a call.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call( $method, $args )
    {
        if( isset( $this->container ) ) {
            return $this->intercept( $method, $args );
        }
        return call_user_func_array( array( $this->obj, $method ), $args );
    }
    /**
     * intercepts a call.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function intercept( $method, $args )
    {
        // before
        $joinPoint = array( $this->name, $method, 'before' );
        $this->container->advice( $joinPoint, $args );
        // around
        try {
            $joinPoint = array( $this->name, $method, 'around' );
            if( $this->container->checkPointCut( $joinPoint ) ) {
                $returned = $this->container->advice( $joinPoint, $args, array( $this->obj, $method ) );
            }
            else {
                $returned = call_user_func_array( array( $this->obj, $method ), $args );
            }
        }
        catch( \Exception $e ) {
            // catch
            $joinPoint = array( $this->name, $method, 'catch' );
            $returned = $this->container->advice( $joinPoint, $args, array( $this->obj, $method ) );
        }
        // after
        $joinPoint = array( $this->name, $method, 'after' );
        $this->container->advice( $joinPoint, $args, NULL, $returned );
        return $returned;
    }

    /**
     * set interceptor at join point to invoke an adviser.
     *
     * @param $method
     * @param $point
     * @param $adviser
     */
    public function setIntercept( $method, $point, $adviser ) {
        $joinPoint = array( $this->name, $method );
        $this->container->setJoinPoint( $joinPoint, $point, $adviser );
    }
}

function j( $joinPoint ) {
    return $joinPoint[0].':'.$joinPoint[1].'@'.$joinPoint[2];
}

class AopContainer
{
    private $advisers = array();
    private $joinPoints = array();

    public function __construct() {}
    /**
     * set join point to an adviser.
     */
    public function setJoinPoint( $joinPoint, $adviser ) {
        $this->joinPoints[ j( $joinPoint ) ][] = $adviser;
    }

    /**
     * check if adviser is registered for this joinPoint.
     */
    public function checkPointCut( $joinPoint ) {
        return isset( $this->joinPoints[ j( $joinPoint ) ] );
    }

    /**
     * set an adviser and its name
     */
    public function setAdviser( $adviser, $advice ) {
        $this->advisers[ $adviser ] = $advice;
    }

    /**
     * give advices from registered adviser.
     *
     * @param array $joinPoint
     * @param array $args
     * @param callable $invoke
     * @param mixed $returned
     * @return mixed
     */
    public function advice( $joinPoint, &$args, $invoke=NULL, $returned=NULL )
    {
        $return = NULL;
        $joinPointJ = j( $joinPoint );
        if( !$this->checkPointCut( $joinPoint ) ) return $returned;
        $adviserList = $this->joinPoints[ $joinPointJ ];
        foreach( $adviserList as $adviser )
        {
            /** @var $adviceObj AopAdviserInterface */
            $adviceObj = $this->advisers[ $adviser ];
            $return = $adviceObj->invoke( $joinPoint, $args, $invoke, $returned );
        }
        return $return;
    }
}

class AopAdviser implements AopAdviserInterface
{
    /**
     * @param array $joinPoint
     * @param array $args
     * @param callable $invoke
     * @param mixed $returned
     * @return mixed
     */
    public function invoke( $joinPoint, $args, $invoke, $returned )
    {
        // do what ever advise to give.

        // for advice type around, it is Adviser's responsibility to call the invoke.
        $return = call_user_func_array( $invoke, $args );

        return $return;
    }
}