<?php

namespace Trienlace\ParseContribuyente;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;

use Trienlace\ParseContribuyente\Exceptions\CodigoErroneoException;
use Trienlace\ParseContribuyente\Exceptions\ContribuyenteNoExisteException;

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

    /** @var  string  $condicion */
    protected $condicion;

    /** @var  string  $html */
    protected $html;

    /**
     * @param  string  $html Respuesta HTML completa del SENIAT
     */
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

        if (mb_strpos($body, 'No existe el contribuyente solicitado') !== false)
        {
            throw new ContribuyenteNoExisteException('El contribuyente no existe.');
        }

        if (mb_strpos($body, 'EL c�digo no coincide con la imagen.') !== false)
        {
            throw new CodigoErroneoException('El código no coincide con la imagen.');
        }

        if (mb_strpos($body, 'EL código no coincide con la imagen.') !== false)
        {
            throw new CodigoErroneoException('El código no coincide con la imagen.');
        }

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
            $condicion = $this->parseCondicion($dom->find('table')[2]);
        }
        elseif ($tablesCount == 4)
        {
            $rif = $this->parseRif($dom->find('table')[1]);
            $razonSocial = $this->parseRazonSocial($dom->find('table')[1]);
            $nombreComercial = $this->parseNombreComercial($dom->find('table')[1]);
            $firmaPersonal = $this->parseFirmaPersonal($dom->find('table')[2]);
            $actividadEconomica = $this->parseActividadEconomica($dom->find('table')[3]);
            $registroVencido = $this->parseRegistroVencido($dom->find('table')[1]);
            $condicion = $this->parseCondicion($dom->find('table')[3]);
        }

        $this->rif                  = $rif;
        $this->razonSocial          = $razonSocial;
        $this->nombreComercial      = $nombreComercial;
        $this->firmaPersonal        = $firmaPersonal;
        $this->actividadEconomica   = $actividadEconomica;
        $this->registroVencido      = $registroVencido;
        $this->condicion            = $condicion;
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

        return mb_strpos($text, 'REGISTRO VENCIDO') !== false;
    }

    /**
     * @param  string  $table
     *
     * @return string
     */
    protected function parseCondicion(string $table)
    {
        $dom = new Dom();
        $dom->setOptions(
            (new Options())
            ->setRemoveStyles(true)
            ->setRemoveScripts(false)
        );
        $dom->loadStr($table);

        $condicionText = $dom->find('font')->innerText();

        $actividadEconomica = $this->parseActividadEconomica($table);

        $condicionText = str_replace('Actividad Económica:', '', $condicionText);
        $condicionText = str_replace('Actividad EconÃ³mica:', '', $condicionText);
        $condicionText = str_replace('Condición:', '', $condicionText);
        $condicionText = str_replace($actividadEconomica, '', $condicionText);

        return trim($condicionText);
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
            'condicion'             => $this->condicion,
        ];
    }
}