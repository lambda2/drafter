<?php

/*
 * This file is part of the moon framework.
 *
 * (c) 2013 Lambdaweb - www.lambdaweb.fr
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author lambda2
 */
class Home extends Controller
{
    /*
     * La fonction d'index.
     * C'est elle qui sera apellée par défaut.
     */

    public function index ($params = array ())
    {
        $quotes = Moon::getAll('c_quote');
        $quote = array_rand($quotes);
        $this->addData('quote', $quote);
        $this->render ();
    }

    public function create ($params = array ())
    {
        $this->render ();
    }

}

?>
