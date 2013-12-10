<?php

/*
 * Copyright 2013 lambda2.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 *
 * This file is part of the moon framework.
 *
 * (c) 2013 Lambdaweb - www.lambdaweb.fr
 *
 * 
 * @author lambda2
 */

class User extends Controller
{

    /*
     * Va etre apellée par défaut.
     */
    public function index ($params)
    {
        $this->registerParams ($params);
        $infos = Moon::get ('c_42_logins', 'login_eleve', $_SESSION['login']);
        $user = Moon::get ('c_user', 'id', $_SESSION['muffin_id']);
	$checkpublic = "";
	if ( $user->comp_public == 1 )
	    $checkpublic = "checked='checked'";
        $formDataJson = $this->generateJsFormData ();
        $checkedRadios = $this->getCheckedRadios ();
        $this->addData ('nom', ucfirst (strtolower ($infos->nom)));
        $this->addData ('formDataJson', $formDataJson);
        $this->addData ('checkedRadios', $checkedRadios);
        $this->addData ('check_public', $checkpublic);
        $this->render ();
    }

    /**
     * @Ajax
     */
    public function me ($params)
    {
        $formDataJson = $this->generateJsFormData ();
        $checkedRadios = $this->getCheckedRadios ();
        $this->addData ('formDataJson', $formDataJson);
        $this->addData ('checkedRadios', $checkedRadios);
        $this->render ('user.index.me');
    }

    public function update ($params)
    {
        $old_pass = $this->filterPost ('o_pass_uid');
        $new_pass = $this->filterPost ('n_pass_uid');
        $new_pass_c = $this->filterPost ('c_pass_uid');
        $public = $this->filterPost ('c_public_uid');
        $this->updatePublic (!isNull ($public));
        if ( !isNull ($old_pass) and !isNull ($new_pass) and !isNull ($new_pass_c) )
        {
            $user = Moon::get ('c_user', 'id', $_SESSION['muffin_id']);
            if ( $user->pass == sha1 ($old_pass) )
            {
                if ( $new_pass == $new_pass_c )
                {
                    Core::getBdd ()->update (
                            array ("pass" => sha1 ($new_pass)), 'c_user', array ("id" => $_SESSION['muffin_id']));
                        echo "1";
                }
                else
                    echo "Les deux mots de passe doivent correspondre";
            }
            else
                echo "Ancien mot de passe incorrect";
        }
        else if ( !isNull ($old_pass) or !isNull ($new_pass) or !isNull ($new_pass_c) )
        {
            echo "Les champs sont incomplets";
        }
        else
        {
            echo "1";
        }
    }

    protected function updatePublic ($public)
    {
        $ret = Core::getBdd ()->update (
                array ("comp_public" => ($public ? "1" : "0")), 'c_user', array ("id" => $_SESSION['muffin_id']));
        return ($ret);
    }

    public function updatecompetence ($params)
    {
        $wtl = $this->filterPost ('want_to_learn');
        $wtt = $this->filterPost ('want_to_teach');
        $id = $this->filterPost ('id_competence');
        $lvl = $this->filterPost ('niveau');

        $assoc = new Entities ("c_user_competences[id_user=\"{$_SESSION['muffin_id']}\"][id_competence=\"{$id}\"]");

        if ( count ($assoc) == 1 )
        {
            $u = array ("niveau" => $lvl,
                "want_to_learn" => $wtl == null ? '0' : $wtl,
                "want_to_teach" => $wtt == null ? '0' : $wtt);
            $res = Core::getBdd ()->update (
                    $u, 'c_user_competences', array ("id_user" => $_SESSION['muffin_id'], "id_competence" => $id));
        }
        else
        {
            $i = array ("id_user" => $_SESSION['muffin_id'],
                "id_competence" => $id, "niveau" => $lvl,
                "want_to_learn" => $wtl == null ? '0' : $wtl,
                "want_to_teach" => $wtt == null ? '0' : $wtt);
            $res = Core::getBdd ()->insert ($i, 'c_user_competences');
        }
        ($res != 0) ? ($ret = "1") : ($ret = "0");
        echo $ret;
    }

    public function deletecompetence ($params)
    {
        $id = $this->filterPost ('id_competence');
        $res = Core::getBdd ()->delete ('c_user_competences', array ("id_user" => $_SESSION['muffin_id'], "id_competence" => $id));
        ($res != 0) ? ($ret = "1") : ($ret = "0");
        echo $ret;
    }

    public function addcompetence ($params)
    {
        $id = false;
        $icone = "sale";
        $nom_joli = strtolower ($this->filterPost ('nom_competence'));
        $desc = strtolower ($this->filterPost ('desc_competence'));
        $nom_brut = '_' . htmlentities (str_replace (
                                array (' ', '+', '#'), array ('_', 'plus', 'diese'), $nom_joli
        ));
        $cmp = new Entities ("c_competences[nom_competence=\"{$nom_brut}\"]");
        if ( preg_match ('/[<>?"\']+/', $nom_joli) > 0 || count ($cmp) > 0 )
        {
            echo "-1";
        }
        else if ( $id = Core::getBdd ()->insert (
                array ("nom_competence" => $nom_brut,
            "nom_usuel" => $nom_joli, "icone" => $icone), 'c_competences') )
        {
            $this->addOrUpdateTags ($id, json_decode ($_POST["modal-new-comp-tags"]));
            $nom_joli = ucfirst (htmlentities ($nom_joli));
            $new = Moon::get ("c_competences", "id_competence", $id);
            echo $this->getJsonCodeForElement ($new);
        }
        else
        {
            echo "0";
        }
    }

    /*   =======================================================================
     *                      Surcharge pour l'accès membre
     *   =======================================================================
     */

    public function grantAccess ()
    {
        if ( isset ($_SESSION['login']) )
            return true;
        else
            return false;
    }

    /*   =======================================================================
     *        Toutes les fonctions concernant les données de formulaire
     *   =======================================================================
     */

    /**
     * Va retourner le bout de code Json compatible [MakeForms] pour la compétence
     * décrite en parametre
     * @param Entity $elt l'objet de l'élément, généré par Moon::getAllHeavy
     * @return string le code Json
     * 
     * @see Moon#getAllHeavy
     */
    protected function getJsonCodeForElement ($elt)
    {
        $bname = htmlentities (ucfirst ($elt->nom_usuel != null ? $elt->nom_usuel : $elt->nom_competence));
        $name = htmlentities ($elt->nom_competence);
        $icone = ($elt->icone != null ? $elt->icone : "uniF002");
        $this->addData ('bname', $bname);
        $this->addData ('name', $name);
        $this->addData ('icon', $icone);
        $this->addData ('id_competence', $elt->id_competence);
        $tpl = $this->getRenderedHtml ("user.form.element");
        return $tpl;
    }

    /**
     * Va retourner le code json de l'ensemble des compétences, et l'assigner
     * à la variable "window.items".
     * @return string le code Json
     */
    protected function generateJsFormData ()
    {
        $datas = array ();
        foreach (Moon::getAllHeavy ("c_competences") as $key => $competence)
        {
            $datas[] = $this->getJsonCodeForElement ($competence);
        }
        return (implode ('', $datas));
    }

    /**
     * Va retourner la liste des radios checkées
     * @return string la liste (json)
     */
    protected function getCheckedRadios ()
    {
        $datas = array ();
        $id_user = $_SESSION['muffin_id'];
        $competences = new Entities ("c_user_competences[id_user=$id_user]");
        foreach ($competences as $key => $competence)
        {
            $nom = $competence->c_competences->nom_competence;
            if ( $competence->want_to_learn == "1" )
                $datas[] = '"wtl_' . $nom . '"';
            if ( $competence->want_to_teach == "1" )
                $datas[] = '"wtt_' . $nom . '"';
            $datas[] = '"' . $nom . "_" . $competence->niveau . '"';
        }
        return implode (",", $datas);
    }

    /*   =======================================================================
     *        Toutes les fonctions concernant les tags de compétences
     *   =======================================================================
     */

    public function tagscompetence ($params)
    {
        $comp = Moon::getAll ('c_categories');
        $tab = array ();
        foreach ($comp as $key => $value)
        {
            $tab[] = '"' . $value["nom"] . '"';
        }
        echo ("[" . implode (",", $tab) . "]");
    }

    protected function addCategorie ($name)
    {
        $i = array ("nom" => $name, "description" => "");
        $res = Core::getBdd ()->insert ($i, 'c_categories');
        return ($res);
    }

    protected function addOrUpdateTags ($id, $tags)
    {
        foreach ($tags as $tag)
        {
            $cat = new Entities ("c_categories[nom=\"{$tag}\"]");

            /** On crée si besoin, et on récupere l'id */
            if ( count ($cat) == 0 )
            {
                $id_cat = $this->addCategorie ($tag);
            }
            else
            {
                $id_cat = $cat->current ()->id_categorie;
            }

            /* On insere le tag */
            $assoc = new Entities ("c_tags[id_competence=\"{$id}\"][id_categorie=\"{$id_cat}\"]");
            if ( count ($assoc) != 1 )
            {
                $i = array ("id_competence" => $id, "id_categorie" => $id_cat);
                $res = Core::getBdd ()->insert ($i, 'c_tags');
            }
        }
    }

}
