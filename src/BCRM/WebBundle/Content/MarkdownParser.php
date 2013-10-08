<?php

namespace BCRM\WebBundle\Content;

use Knp\Bundle\MarkdownBundle\Parser\MarkdownParser as BaseParser;

/**
 * Markdown Parser for parsing the content files.
 */
class MarkdownParser extends BaseParser
{
    /**
     * @var array Enabled features
     */
    protected $features = array(
        'header'            => true,
        'list'              => true,
        'horizontal_rule'   => true,
        'table'             => true,
        'foot_note'         => true,
        'fenced_code_block' => true,
        'abbreviation'      => true,
        'definition_list'   => true,
        'inline_link'       => true,
        'reference_link'    => true,
        'shortcut_link'     => true,
        'block_quote'       => true,
        'code_block'        => true,
        'html_block'        => true,
        'auto_link'         => true,
        'auto_mailto'       => true,
        'entities'          => true,
        'no_html'           => false,
    );
}
