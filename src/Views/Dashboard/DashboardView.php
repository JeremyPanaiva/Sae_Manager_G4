<?php

namespace Views\Dashboard;

use Views\Base\BaseView;

class DashboardView extends BaseView
{
    public const TITLE_KEY = 'TITLE_KEY';
    public const CONTENT_KEY = 'CONTENT_KEY';
    public const USERNAME_KEY = 'USERNAME_KEY';
    public const ROLE_KEY = 'ROLE_KEY';

    private string $title;
    private string $username;
    private string $role;
    private array $data;

    /**
     * @param string $title - titre de la page
     * @param array $data - données pour le dashboard
     * @param string $username - nom complet utilisateur
     * @param string $role - rôle de l'utilisateur
     */
    public function __construct(string $title, array $data, string $username, string $role)
    {
        $this->title = $title;
        $this->data = $data;
        $this->username = $username;
        $this->role = $role;
    }

    public function templatePath(): string
    {
        return __DIR__ . '/dashboard.html';
    }

    public function templateKeys(): array
    {
        $contentHtml = $this->buildContentHtml();

        // Récupère le header
        $headerView = new \Views\Base\HeaderView();
        $headerKeys = $headerView->templateKeys();

        return array_merge($headerKeys, [
            self::TITLE_KEY => $this->title,
            self::CONTENT_KEY => $contentHtml,
            self::USERNAME_KEY => $this->username,
            self::ROLE_KEY => $this->role,
        ]);
    }

    /**
     * Génère le HTML du contenu selon le rôle
     */
    private function buildContentHtml(): string
    {
        $html = '';

        switch (strtolower($this->role)) {

            case 'etudiant':
                $html .= "<h2>Vos SAE attribuées</h2>";

                foreach ($this->data['saes'] ?? [] as $sae) {
                    $html .= "<div class='dashboard-card'>";

                    // --- Titre de la SAE ---
                    $titreSae = htmlspecialchars($sae['sae_titre'] ?? 'Titre inconnu');
                    $html .= "<h3>{$titreSae}</h3>";

                    // --- Date de rendu avec compte à rebours ---
                    $dateRendu = $sae['date_rendu'] ?? '';
                    $html .= "<p><strong>Date de rendu :</strong> {$dateRendu} ";
                    $html .= "<span class='countdown' data-date='{$dateRendu}'></span></p>";

                    // --- Avancement To-Do List ---
                    $todos = $sae['todos'] ?? [];
                    $totalTasks = count($todos);
                    $doneTasks = count(array_filter($todos, fn($task) => !empty($task['fait'])));
                    $percent = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                    $html .= "<p><strong>Avancement :</strong> {$percent}%</p>";

                    // --- Barre de progression ---
                    $html .= "<div class='progress-bar'>";
                    $html .= "<div class='progress-fill' style='width: {$percent}%;'></div>";
                    $html .= "</div>";

                    // --- Formulaire pour ajouter une tâche ---
                    $saeAttributionId = $sae['sae_attribution_id'] ?? 0;
                    $html .= "<form method='POST' action='/todo/add' class='todo-add'>";
                    $html .= "<input type='hidden' name='sae_attribution_id' value='{$saeAttributionId}'>";
                    $html .= "<input type='text' name='titre' placeholder='Nouvelle tâche...' required>";
                    $html .= "<button type='submit'>Ajouter</button>";
                    $html .= "</form>";

                    // --- Liste des tâches ---
                    if ($totalTasks > 0) {
                        $html .= "<ul class='todo-list'>";
                        foreach ($todos as $task) {
                            $taskId = $task['id'] ?? 0;
                            $taskTitre = htmlspecialchars($task['titre'] ?? 'Tâche');
                            $fait = !empty($task['fait']);
                            $checked = $fait ? 'checked' : '';

                            $html .= "<li>";
                            $html .= "<form method='POST' action='/todo/toggle' class='todo-toggle'>";
                            $html .= "<input type='hidden' name='task_id' value='{$taskId}'>";
                            $html .= "<input type='hidden' name='fait' value='" . ($fait ? 0 : 1) . "'>";
                            $html .= "<label>";
                            $html .= "<input type='checkbox' class='todo-checkbox' onclick='this.form.submit();' {$checked}> ";
                            $html .= $taskTitre;
                            $html .= "</label>";
                            $html .= "</form>";
                            $html .= "</li>";
                        }
                        $html .= "</ul>";
                    } else {
                        $html .= "<p>Aucune tâche pour cette SAE.</p>";
                    }
                    // --- Étudiants associés ---
                    $etudiants = $sae['etudiants'] ?? [];
                    if (!empty($etudiants)) {
                        $html .= "<h4>Autres étudiants associés :</h4>";
                        $html .= "<ul class='student-list'>";
                        foreach ($etudiants as $etudiant) {
                            $nomComplet = htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']);
                            $html .= "<li>{$nomComplet}</li>";
                        }
                        $html .= "</ul>";
                    }



                    // --- Avis / remarques ---
                    if (!empty($sae['avis'])) {
                        $html .= "<h4>Remarques</h4>";
                        foreach ($sae['avis'] as $avis) {
                            $emetteur = htmlspecialchars(ucfirst($avis['emetteur'] ?? ''));
                            $message = htmlspecialchars($avis['message'] ?? '');
                            $dateAvis = htmlspecialchars($avis['date_envoi'] ?? '');

                            $html .= "<div class='avis-card'>";
                            $html .= "<p><strong>{$emetteur} :</strong> {$message}</p>";
                            $html .= "<small>{$dateAvis}</small>";
                            $html .= "</div>";
                        }
                    }

                    $html .= "</div>"; // dashboard-card
                }
                break;




            case 'client':
                $html .= "<h2>Vos SAE créées</h2>";

                foreach ($this->data['saes'] ?? [] as $sae) {
                    $html .= "<div class='dashboard-card'>";
                    $html .= "<h3>" . htmlspecialchars($sae['titre']) . "</h3>";
                    $html .= "<p><strong>Description :</strong> " . htmlspecialchars($sae['description']) . "</p>";
                    $html .= "<p><strong>Date de création :</strong> " . htmlspecialchars($sae['date_creation']) . "</p>";

                    // Lien pour voir les attributions (optionnel)
                    $html .= "<a href='/sae_attributions/{$sae['id']}' class='btn btn-outline'>Voir les attributions</a>";

                    // Avis / remarques des responsables
                    $html .= "<h4>Remarques</h4>";
                    foreach ($sae['avis'] ?? [] as $avis) {
                        $html .= "<div class='avis-card'>";
                        $html .= "<p><strong>" . ucfirst($avis['emetteur']) . " :</strong> " . htmlspecialchars($avis['message']) . "</p>";
                        $html .= "<small>" . htmlspecialchars($avis['date_envoi']) . "</small>";
                        $html .= "</div>";
                    }

                    $html .= "</div>";
                }
                break;

            case 'responsable':
                $html .= "<h2>Vos SAE attribuées</h2>";

                foreach ($this->data['saes'] ?? [] as $sae) {
                    $html .= "<div class='dashboard-card'>";

                    // --- Titre de la SAE ---
                    $titreSae = htmlspecialchars($sae['sae_titre'] ?? 'Titre inconnu');
                    $html .= "<h3>{$titreSae}</h3>";

                    // --- Étudiants associés ---
                    $etudiants = $sae['etudiants'] ?? [];
                    if (!empty($etudiants)) {
                        $etudiantsList = [];
                        foreach ($etudiants as $etu) {
                            $etudiantsList[] = htmlspecialchars(($etu['nom'] ?? '') . ' ' . ($etu['prenom'] ?? ''));
                        }
                        $html .= "<p><strong>Étudiants :</strong> " . implode(', ', $etudiantsList) . "</p>";
                    } else {
                        $html .= "<p><strong>Étudiants :</strong> Aucun</p>";
                    }

                    // --- Date de rendu modifiable ---
                    $saeAttributionId = $sae['sae_attribution_id'] ?? 0;
                    $dateRendu = htmlspecialchars($sae['date_rendu'] ?? '');
                    $html .= "<form method='POST' action='/sae/update_date' class='update-date-form'>";
                    $html .= "<input type='hidden' name='sae_attribution_id' value='{$saeAttributionId}'>";
                    $html .= "<input type='date' name='date_rendu' value='{$dateRendu}'>";
                    $html .= "<button type='submit'>Modifier la date de rendu</button>";
                    $html .= "</form>";

                    // --- To-Do list et barre de progression ---
                    $todos = $sae['todos'] ?? [];
                    if (!empty($todos)) {
                        $totalTasks = count($todos);
                        $doneTasks = count(array_filter($todos, fn($task) => !empty($task['fait'])));
                        $percent = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;

                        // Barre de progression
                        $html .= "<p><strong>Avancement :</strong> {$percent}%</p>";
                        $html .= "<div class='progress-bar'>";
                        $html .= "<div class='progress-fill' style='width: {$percent}%;'></div>";
                        $html .= "</div>";

                        // Liste des tâches (lecture seule)
                        $html .= "<ul class='todo-list'>";
                        foreach ($todos as $task) {
                            $taskTitre = htmlspecialchars($task['titre'] ?? 'Tâche');
                            $fait = !empty($task['fait']);
                            $html .= "<li>{$taskTitre}" . ($fait ? " ✅" : "") . "</li>";
                        }
                        $html .= "</ul>";
                    } else {
                        $html .= "<p>Aucune tâche pour cette SAE.</p>";
                    }

                    $html .= "</div>"; // dashboard-card
                }
                break;





            default:
                $html .= "<p>Rôle inconnu ou aucune donnée disponible.</p>";
        }


        return $html;
    }
}
