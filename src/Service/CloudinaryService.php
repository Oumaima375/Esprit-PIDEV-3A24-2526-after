<?php
// src/Service/CloudinaryService.php

namespace App\Service;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => 'dweak2d2b',
                'api_key'    => '537584949457259',
                'api_secret' => 'TWqDAC4ht95xuN5Gw-TwpqFZZhw',
            ],
            'url' => ['secure' => true]
        ]);
    }

    public function upload(string $filePath, string $nomOriginal = ''): string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload($filePath, [
                'folder'        => 'after-travel/documents',
                'public_id'     => uniqid('doc_') . '_' . time(),
                'resource_type' => 'auto', // supporte PDF + images
                'tags'          => ['after-travel', 'document'],
            ]);

            error_log('☁️ Cloudinary OK: ' . $result['secure_url']);
            return $result['secure_url'];

        } catch (\Exception $e) {
            error_log('☁️ Cloudinary erreur: ' . $e->getMessage());
            throw new \RuntimeException('Erreur upload Cloudinary: ' . $e->getMessage());
        }
    }

    public function supprimer(string $publicId): void
    {
        try {
            $this->cloudinary->uploadApi()->destroy($publicId);
            error_log('☁️ Cloudinary suppression OK: ' . $publicId);
        } catch (\Exception $e) {
            error_log('☁️ Cloudinary suppression erreur: ' . $e->getMessage());
        }
    }
}