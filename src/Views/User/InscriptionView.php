<?php

namespace Views\User;

use Views\Base\BaseView;

class InscriptionView extends BaseView {

    // Champs du formulaire
    public const NOM_KEY = 'NOM_KEY';
    public const PRENOM_KEY = 'PRENOM_KEY';
    public const MAIL_KEY = 'MAIL_KEY';
    public const PASSWORD_KEY = 'PASSWORD_KEY';

    // Chemin du template
    private const TEMPLATE_HTML = __DIR__ . '/inscription.html';

    public function templatePath(): string {
        return self::TEMPLATE_HTML;
    }

    public function templateKeys(): array {
        return [
            self::NOM_KEY => 'nom',
            self::PRENOM_KEY => 'prenom',
            self::MAIL_KEY => 'mail',
            self::PASSWORD_KEY => 'mdp'
        ];
    }
}
