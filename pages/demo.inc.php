<?php
/*
mform demo.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

$mdl_im ='<mform>
text|1|label1|REX_VALUE[1]
text|2|label2|REX_VALUE[2]|input-css-classe|width:200px;

html|<strong>Demo Eingabe</strong>

media|1|Media Label1|REX_FILE[1]|2|preview=1;types=jpg,png,gif
media|2|Media Label2|REX_FILE[2]

description|Beschreibungstext zum Media Label2

headline|Demo Headline

medialist|1|label3|REX_MEDIALIST[1]|2|preview=1;types=jpg,png,gif
</mform>';

$phpmarkitup = <<<EOT
<?php
// define module Settings
$arrMarkitupSettings = array(
  'markitup-width'       => '710',
  'markitup-height'      => '480',
  'markitup-buttons'     => '
     
      h1,h2,h3,h4,h5,h6,separator
      ,bold,italic,underline,stroke,separator
      ,alignments,color,separator
      ,listbullet,listnumeric,separator
      ,table,separator
      ,subscript,superscript,intlink,extlink,separator
      ,mailtolink,filelink,separator
      ,blockquote,preview
     
  '
);

a287_markitup::markitup(
  'textarea.markitup1',str_replace(array(' ',"&#92;n"),
  '',
EOT;
$phpmarkitup .= '$arrMarkitupSettings["markitup-buttons"]),
  $arrMarkitupSettings["markitup-width"],
  $arrMarkitupSettings["markitup-height"]';
$phpmarkitup .= <<<EOT

);
?>
EOT;

$mformschema = <<<EOT
Headlines, HTML, Descirptions:

typ|Text

  -> headline|Demo Headline
  -> html|<strong>Demo HTML</strong>
  -> description|Demo Beschreibung


----------------------------------------------------------


Text, Hidden, Textarea, Markitup Input:

typ|valueId|label|defaultValue|classe|css|

  -> text|1|Name|REX_VALUE[1]|css-classe-input|width:400px
  -> hidden|1|Name|REX_VALUE[2]|css-classe-hidden
  -> textarea|2|Message|REX_VALUE[3]|css-classe-textarea|width:300px;height:190px
  -> markitup|3|Message|REX_VALUE[4]|css-classe-markitup|<?php echo implode(';',&#36;arrMarkitupSettings); ?>


----------------------------------------------------------


Select, Multiselect, Checkboxen, Radio Buttons:

typ|valueId|label|defaultValue|parameter|classe|css|

  -> select|5|label9|REX_VALUE[5]|1=test1;2=test3;3=test4||width:400px
  -> multiselect|6|label9|REX_VALUE[6]|1=test1;2=test3;3=test4||width:400px

typ|valueId|label|defaultValue|parameter|

  -> checkbox|7|checkboxlabel|REX_VALUE[7]|checkdefaultvalue=checkboxdesc
  -> radio|8|radiolabel|REX_VALUE[8]|1=test1;2=test2;3=test3


----------------------------------------------------------


Media, Medialist, Link, Linklist Buttons:

media|mediaId|label|rex_file|mediacategoryId|settings
medialist|mediaId|label|rex_medialist|mediacategoryId|settings

  -> media|1|Bildelement|REX_FILE[1]|2|preview=1;types=jpg,png,gif
  -> medialist|1|Bildelemente|REX_MEDIALIST[1]|2|preview=1;types=jpg,png,gif

link|linkId|label|rex_link|categoryId
linklist|linkId|label|rex_linklist|categoryId

  -> link|1|Interner Link|REX_LINK_ID[1]|2
  -> linklist|1|Interne Links|REX_LINKLIST[1]|2
EOT;
