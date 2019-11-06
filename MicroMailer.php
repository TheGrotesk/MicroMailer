<?php

namespace Utils;

use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Mailer
{
    /**
     * @var
     */
    private $config;

    /**
     * @var
     */
    private $mailer;

    /**
     * @var
     */
    private $body;


    public function __construct(array $config)
    {
            $this->config = $config;

            $transport = (new Swift_SmtpTransport($config['mailer']['smtp'], 587))
                ->setUsername($config['mailer']['email'])
                ->setPassword($config['mailer']['password'])
                ->setEncryption("tls");

            $this->mailer = new Swift_Mailer($transport);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $args
     * @param array|null $options
     * @return $this
     * @throws \Exception
     */
    public function generateBody(string $to, string $subject, string $template, array $args, array $options = null)
    {
        if (isset($options['localize']) && $options['localize'] == true) {
            $dom = $this->getHtmlDomContent($template);

            $dom = $this->getHtmlElementsWithLocalize($dom, $options['lang']);

            $temp_body = $this->saveTempTemplate($dom);
        } else {
            $temp_body = $this->getTemplate($template);
        }

        $keys = array_keys($args);

        array_walk($keys, function (&$item, $key) {
            $item = "{{ $item }}";
        });

        $final_body = str_replace($keys, array_values($args), $temp_body);

        $this->body = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([$this->config['mailer']['mail']['sender_email'] => 'Main Question'])
            ->setTo([$to])
            ->setBody($final_body, 'text/html');

        return $this;
    }

    /**
     * @param string $template
     * @return array|false
     * @throws \Exception
     */
    private function getTemplate(string $template)
    {
        $path = $this->config['mailer']['template_path'].$template.'.html';

        if (!file_exists($path)) {
            throw new \Exception("Template not found!", 404);
        }

        $content = file($path);

        return $content;
    }

    /**
     * @param string $template
     * @return \DOMDocument
     * @throws \Exception
     */
    private function getHtmlDomContent(string $template)
    {
        $path = $this->config['mailer']['template_path'].$template.'.html';

        if (!file_exists($path)) {
            throw new \Exception("Template not found!", 404);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(file_get_contents($path));
        $dom->encoding = 'utf-8';

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     * @param $lang
     * @return \DOMDocument
     */
    private function getHtmlElementsWithLocalize(\DOMDocument $dom, $lang)
    {
        $this->setLocale($lang);

        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//*[@data-localize]') as $rowNode) {
            $rowNode->nodeValue = gettext($rowNode->nodeValue);
        }

        return $dom;
    }

    /**
     * @param \DOMDocument $dom
     * @return string
     * @throws \Exception
     */
    private function saveTempTemplate(\DOMDocument $dom)
    {
        try {
            $dom->formatOutput = true;

            $html = $dom->saveXML($dom->documentElement);
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        return $html;
    }

    /**
     * @param $lang
     */
    private function setLocale($lang)
    {
        $locale = $this->config['locales'][$lang];
        putenv("LC_ALL=$locale");
        putenv("LANGUAGE=$locale");
        setlocale(LC_COLLATE, $locale);
        setlocale(LC_ALL, $locale);
        bindtextdomain($this->config['localize']['text_domain'], $this->config['localize']['locale_path']);
        bind_textdomain_codeset($this->config['localize']['text_domain'], 'UTF-8');
        textdomain($this->config['localize']['text_domain']);
    }

    /**
     * @return int
     */
    public function send()
    {
        $send = $this->mailer->send($this->body);

        return $send;
    }
}
