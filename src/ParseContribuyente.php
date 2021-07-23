<?php

namespace Trienlace\ParseContribuyente;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

class ParseContribuyente
{
    /** @var  string  $rif */
    protected $rif;

    /** @var  string  $razonSocial */
    protected $razonSocial;

    /** @var  string  $nombreComercial */
    protected $nombreComercial;

    /** @var  array  $firmaPersonal */
    protected $firmaPersonal;

    /** @var  string  $actividadEconomica */
    protected $actividadEconomica;

    /** @var  boolean  $registroVencido */
    protected $registroVencido;

    /** @var  string  $html */
    protected $html;

    public function __construct(string $html)
    {
        $this->html = $html;

        $this->parseContribuyente($this->html);
    }

    /**
     * @param  string  $body
     *
     * @return array
     */
    protected function parseContribuyente(string $body)
    {
        $body = utf8_encode($body);
        // $body = explode('<!-- VISUALIZAR RIF -->', $body)[1];
        $body = str_replace('windows-1252', 'UTF-8', $body);
        $body = str_replace('</HTML>', '', $body);
        $body = trim($body);

        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($body);

        $tablesCount = $dom->find('table')->count();

        $rif = '';
        $razonSocial = '';
        $nombreComercial = '';
        $firmaPersonal = [];
        $actividadEconomica = '';
        $registroVencido = false;

        if ($tablesCount == 3)
        {
            $rif = $this->parseRif($dom->find('table')[1]);
            $razonSocial = $this->parseRazonSocial($dom->find('table')[1]);
            $nombreComercial = $this->parseNombreComercial($dom->find('table')[1]);
            $firmaPersonal = [];
            $actividadEconomica = $this->parseActividadEconomica($dom->find('table')[2]);
            $registroVencido = $this->parseRegistroVencido($dom->find('table')[1]);
        }
        elseif ($tablesCount == 4)
        {
            $rif = $this->parseRif($dom->find('table')[1]);
            $razonSocial = $this->parseRazonSocial($dom->find('table')[1]);
            $nombreComercial = $this->parseNombreComercial($dom->find('table')[1]);
            $firmaPersonal = $this->parseFirmaPersonal($dom->find('table')[2]);
            $actividadEconomica = $this->parseActividadEconomica($dom->find('table')[3]);
            $registroVencido = $this->parseRegistroVencido($dom->find('table')[1]);
        }

        $this->rif                  = $rif;
        $this->razonSocial          = $razonSocial;
        $this->nombreComercial      = $nombreComercial;
        $this->firmaPersonal        = $firmaPersonal;
        $this->actividadEconomica   = $actividadEconomica;
        $this->registroVencido      = $registroVencido;
    }

    /**
     * @param  string  $table
     *
     * @return string
     */
    protected function parseRif(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $fontText = $dom->find('font')->innerText();

        $rif = explode('&nbsp;', $fontText)[0];



        return trim($rif);

    }

    /**
     * @param  string  $table
     *
     * @return string
     */
    protected function parseRazonSocial(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $fontText = $dom->find('font')->innerText();

        $razonSocial = explode('(', $fontText)[0];

        return trim(explode('&nbsp;', $razonSocial)[1]);
    }

    /**
     * @param  string  $table
     *
     * @return string
     */
    protected function parseNombreComercial(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $fontText = $dom->find('font')->innerText();
        $fontText = str_replace(')', '', $fontText);

        $nombreArray = explode('(', $fontText);

        if (count($nombreArray) > 1)
        {
            $nombreComercial = $nombreArray[1];
        }
        else
        {
            $nombreComercial = $this->parseRazonSocial($table);
        }

        if (mb_strpos($nombreComercial, 'SIN NOMBRE COMERCIAL') !== false)
        {
            $nombreComercial = $this->parseRazonSocial($table);
        }

        return trim($nombreComercial);
    }

    /**
     * @param  string  $table
     *
     * @return \Illuminate\Support\Collection
     */
    protected function parseFirmaPersonal(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $firmas = $dom->find('td[align="center"]');

        return array_map(function ($item) {
            return [
                'nombre' => trim($item->innerText())
            ];
        }, $firmas->toArray());
    }

    /**
     * @param  string  $table
     *
     * @return string
     */
    protected function parseActividadEconomica(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $actividadEconomicaText = $dom->find('font')->firstChild()->text();
        $actividadEconomicaText = str_replace('Actividad Económica:', '', $actividadEconomicaText);
        $actividadEconomicaText = str_replace('Actividad EconÃ³mica:', '', $actividadEconomicaText);

        return trim($actividadEconomicaText);
    }

    /**
     * @param  string  $table
     *
     * @return boolean
     */
    protected function parseRegistroVencido(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $text = $dom->find('b')[0]->innerText();

        return str_contains($text, 'REGISTRO VENCIDO');
    }

    /**
     * @return array
    */
    public function toArray()
    {
        return [
            'rif'                   => $this->rif,
            'razon_social'          => $this->razonSocial,
            'nombre_comercial'      => $this->nombreComercial,
            'firma_personal'        => $this->firmaPersonal,
            'actividad_economica'   => $this->actividadEconomica,
            'registro_vencido'      => $this->registroVencido,
        ];
    }
}