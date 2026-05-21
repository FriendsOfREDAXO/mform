<?php

$title = 'REX_VALUE[1]';
$teaser = 'REX_VALUE[2]';
$metaTitle = 'REX_VALUE[3]';
$metaDescription = 'REX_VALUE[4]';
$ctaLink = 'REX_VALUE[5]';
$ctaText = 'REX_VALUE[6]';

echo '<div class="panel panel-default" style="margin:1rem">';
echo '<div class="panel-heading"><strong>Tabs modern + vertikal (Demo)</strong></div>';
echo '<div class="panel-body">';
echo '<p><strong>Titel:</strong> ' . htmlspecialchars($title) . '</p>';
echo '<p><strong>Teaser:</strong><br>' . nl2br(htmlspecialchars($teaser)) . '</p>';
echo '<p><strong>Meta-Title:</strong> ' . htmlspecialchars($metaTitle) . '</p>';
echo '<p><strong>Meta-Description:</strong><br>' . nl2br(htmlspecialchars($metaDescription)) . '</p>';
echo '<p><strong>CTA-Link:</strong> ' . htmlspecialchars($ctaLink) . '</p>';
echo '<p><strong>Button-Text:</strong> ' . htmlspecialchars($ctaText) . '</p>';
echo '</div></div>';
