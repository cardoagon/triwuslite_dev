<?php

/*************************************************

    Triwus Framework v.0

    Elmt_indice.php

    Author: Carlos Domingo Arias González

    Copyright (C) 2011

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation version 3.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*************************************************/


final class Elmt_indice extends Elmt_texto {
  public function __construct(Indice_obd $pe_obd) {
    parent::__construct($pe_obd);
  }

  public function elmt_obd() {
    $etxt = parent::elmt_obd();

    $e = new Indice_obd();

    $e->atr("ref_site")->valor = $etxt->atr("ref_site")->valor;
    $e->atr("txt"     )->valor = $etxt->atr("txt"     )->valor;
    $e->atr("wysiwyg" )->valor = $etxt->atr("wysiwyg" )->valor;

    return $this->obd_base($e);
  }

  public static function __indice(Site_obd $s, $id_idioma) {
    if (($id_site = $s->atr("id_site")->valor) == null) return null;

    $url_limpa = $s->atr("url_limpa")->valor();

    $html = null;

    $cbd = new FS_cbd();

    $sql = "select id_opcion, id_paxina, tipo, nome_paxina
              from v_site_opcion
             where id_site = {$id_site}
               and id_idioma = '{$id_idioma}'
               and pai is null
               and tipo in ('libre','novas','ventas')
             order by posicion";

    $r = $cbd->consulta($sql);

    while ($_a = $r->next()) $html .= self::__indice_aux($cbd, $url_limpa, $_a, $html);


    return "<ol class='celda_elmt_texto_uol'>{$html}</ol>";
  }

  private static function __indice_aux(FS_cbd $cbd, $url_limpa, $_p, $html = "") {
    $html = self::__l($cbd, $url_limpa, $_p);

    $sql = "select id_opcion, id_paxina, tipo, nome_paxina
              from v_site_opcion where pai = {$_p['id_opcion']}
          order by posicion";

    $r = $cbd->consulta($sql);

    for ($html_aux  = "";
         $_a        = $r->next();
         $html_aux .= self::__indice_aux($cbd, $url_limpa, $_a, $html_aux)
        );

    return "<li>{$html}</li><ol class='celda_elmt_texto_uol'>{$html_aux}</ol>";
  }

  private static function __l(FS_cbd $cbd, $url_limpa, $_p) {
    $p = Paxina_obd::inicia($cbd, $_p['id_paxina'], $_p['tipo']);


    $l = new Link($id, $_p["nome_paxina"], "_blank", $p->action_2($url_limpa));


    return $l->html();
  }
}

