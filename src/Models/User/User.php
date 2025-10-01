<?php

namespace Models\User;

class User {
    private $id;
    private $nom;
    private $prenom;
    private $mail;
    private $mdp;

    public function getId(): int {
        return $this->id;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function getPrenom(): ?string {
        return $this->prenom;
    }


    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    // autres setters/getters existants...
}
