<?php

final class CRedesSoc extends Componente implements ICPC {
  private $id_site = null;

  public function __construct(Site_obd $s) {
    parent::__construct("credessoc", "ptw/paneis/pcontrol/redessoc.html");

    $this->pon_obxeto(Panel_fs::__baceptar());
    $this->pon_obxeto(Panel_fs::__bcancelar());


    $this->__inicia( $s );
  }

  public function operacion(EstadoHTTP $e) {
    if ($this->obxeto("bCancelar")->control_evento()) {
      $this->__inicia( Site_obd::inicia(new FS_cbd(), $this->id_site) );

      return $e;
    }

    if ($this->obxeto("bAceptar")->control_evento()) {
      $this->update( new FS_cbd() );

      return $e;
    }

    return null;
  }

  public function cpc_miga() {
    return "redes sociales";
  }

  public function update(FS_cbd $cbd) {
    $sc = Site_config_obd::inicia($cbd, $this->id_site);

    $sc->atr("rs_applemusic_url" )->valor = trim($this->obxeto("rs_applemusic_url" )->valor());
    $sc->atr("rs_facebook_url"   )->valor = trim($this->obxeto("rs_facebook_url"   )->valor());
    $sc->atr("rs_twitter_url"    )->valor = trim($this->obxeto("rs_twitter_url"    )->valor());
    $sc->atr("rs_linkedin_url"   )->valor = trim($this->obxeto("rs_linkedin_url"   )->valor());
    $sc->atr("rs_vimeo_url"      )->valor = trim($this->obxeto("rs_vimeo_url"      )->valor());
    $sc->atr("rs_flickr_url"     )->valor = trim($this->obxeto("rs_flickr_url"     )->valor());
    $sc->atr("rs_tumblr_url"     )->valor = trim($this->obxeto("rs_tumblr_url"     )->valor());
    $sc->atr("rs_youtube_url"    )->valor = trim($this->obxeto("rs_youtube_url"    )->valor());
    $sc->atr("rs_instagram_url"  )->valor = trim($this->obxeto("rs_instagram_url"  )->valor());
    $sc->atr("rs_tripadvisor_url")->valor = trim($this->obxeto("rs_tripadvisor_url")->valor());
    $sc->atr("rs_pinterest_url"  )->valor = trim($this->obxeto("rs_pinterest_url"  )->valor());
    $sc->atr("rs_blogger_url"    )->valor = trim($this->obxeto("rs_blogger_url"    )->valor());
    $sc->atr("rs_telegram_url"   )->valor = trim($this->obxeto("rs_telegram_url"   )->valor());
    $sc->atr("rs_tiktok_url"     )->valor = trim($this->obxeto("rs_tiktok_url"     )->valor());
    $sc->atr("rs_spotify_url"    )->valor = trim($this->obxeto("rs_spotify_url"    )->valor());
    $sc->atr("rs_bluesky_url"    )->valor = trim($this->obxeto("rs_bluesky_url"    )->valor());


    return $sc->update($cbd);
  }

  private function __inicia(Site_obd $s) {
    $this->id_site = $s->atr("id_site")->valor;

    $cbd = new FS_cbd();

    $sc = Site_config_obd::inicia($cbd, $this->id_site);

    $this->pon_obxeto(self::__text("rs_facebook_url"   , $sc));
    $this->pon_obxeto(self::__text("rs_twitter_url"    , $sc));
    $this->pon_obxeto(self::__text("rs_linkedin_url"   , $sc));
    $this->pon_obxeto(self::__text("rs_vimeo_url"      , $sc));
    $this->pon_obxeto(self::__text("rs_flickr_url"     , $sc));
    $this->pon_obxeto(self::__text("rs_tumblr_url"     , $sc));
    $this->pon_obxeto(self::__text("rs_youtube_url"    , $sc));
    $this->pon_obxeto(self::__text("rs_instagram_url"  , $sc));
    $this->pon_obxeto(self::__text("rs_tripadvisor_url", $sc));
    $this->pon_obxeto(self::__text("rs_pinterest_url"  , $sc));
    $this->pon_obxeto(self::__text("rs_blogger_url"    , $sc));
    $this->pon_obxeto(self::__text("rs_telegram_url"   , $sc));
    $this->pon_obxeto(self::__text("rs_tiktok_url"     , $sc));
    $this->pon_obxeto(self::__text("rs_spotify_url"    , $sc));
    $this->pon_obxeto(self::__text("rs_bluesky_url"    , $sc));
    $this->pon_obxeto(self::__text("rs_applemusic_url" , $sc));

    $this->pon_obxeto(self::__ico("rs_facebook_url"   , "facebook"   ));
    $this->pon_obxeto(self::__ico("rs_twitter_url"    , "X (twitter)"    ));
    $this->pon_obxeto(self::__ico("rs_linkedin_url"   , "linkedin"   ));
    $this->pon_obxeto(self::__ico("rs_vimeo_url"      , "vimeo"      ));
    $this->pon_obxeto(self::__ico("rs_flickr_url"     , "flickr"     ));
    $this->pon_obxeto(self::__ico("rs_tumblr_url"     , "tumblr"     ));
    $this->pon_obxeto(self::__ico("rs_youtube_url"    , "youtube"    ));
    $this->pon_obxeto(self::__ico("rs_instagram_url"  , "instagram"  ));
    $this->pon_obxeto(self::__ico("rs_tripadvisor_url", "tripadvisor"));
    $this->pon_obxeto(self::__ico("rs_pinterest_url"  , "pinterest"  ));
    $this->pon_obxeto(self::__ico("rs_blogger_url"    , "blogger"    ));
    $this->pon_obxeto(self::__ico("rs_telegram_url"   , "telegram"   ));
    $this->pon_obxeto(self::__ico("rs_tiktok_url"     , "TikTok"     ));
    $this->pon_obxeto(self::__ico("rs_spotify_url"    , "spotify"    ));
    $this->pon_obxeto(self::__ico("rs_bluesky_url"    , "bluesky"    ));
    $this->pon_obxeto(self::__ico("rs_applemusic_url" , "apple music"));
  }

  private static function __text($id, Site_config_obd $sc) {
    $t = CMetatags::__text($id, $sc->atr($id)->valor, " ");

    $t->style("default", "border: 0;");

    return $t;
  }

  private static function __ico($id, $titulo) {
    $i = new Image("ico_{$id}", Config_obd::url_imx_rs . "1/{$id}.png", false);

    $i->title = $titulo;

    $i->style("default", "width: 23px;");

    return $i;
  }
}
