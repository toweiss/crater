# Anleitung: AGB-PDF aktualisieren

## Überblick

Die AGB werden automatisch an jedes Estimate-PDF angehängt. Diese Anleitung beschreibt, wie Sie die AGB-Datei aktualisieren können.

## Aktueller Stand

- **Aktueller Speicherort**: `/public/uploads/docs/whitemacs_AGB_V1.pdf`
- **Zuständiger Service**: `app/Services/PdfWithAgbService.php`
- **Controller**: `app/Http/Controllers/CustomEstimatePdfController.php`

## Schritte zur Aktualisierung

### 1. Neue AGB über SCP auf Server schicken
>> scp .\whitemacs_AGB_V1.pdf crater_user@217.154.70.12:~/crater/public/uploads/docs

### 2. Gegebenenfalls Namen in .env aktualisieren.