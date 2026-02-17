<?php

namespace App\Services\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;

class QRCodeService
{
    /**
     * Génère le flux SVG pour une étiquette d'article.
     * Le QR Code contient l'identifiant interne qr_code_base.
     */
    public function generateArticleLabel(Article $article): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter)
            ->writerOptions([])
            ->data($article->qr_code_base ?? $article->sku)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->build();

        return $result->getString();
    }

    /**
     * Génère l'étiquette pour un numéro de série spécifique.
     * Applique la charte graphique Batistack.
     */
    public function generateSNLabel(ArticleSerialNumber $sn): string
    {
        $result = Builder::create()
            ->writer(new SvgWriter)
            ->data("SN:{$sn->serial_number}")
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(150)
            ->margin(10)
            ->foregroundColor(new Color(0, 33, 87)) // Bleu Institutionnel Batistack
            ->backgroundColor(new Color(255, 255, 255))
            ->build();

        return $result->getString();
    }
}
