# 🏥 DOSSIER DE PRÉSENTATION & SPÉCIFICATIONS TECHNIQUES
## Système Intelligent de Gestion des Files d'Attente et des Flux Patients
**Centre Hospitalier Universitaire (CHU) du Point G — Bamako**

---

## 📋 Sommaire Exécutif

1. **Contexte & Enjeux Stratégiques**
2. **Architecture des Rôles : « Qui fait quoi ? »**
3. **Modèle de Données & Diagramme de Classes UML**
4. **Diagramme des Cas d'Utilisation UML**
5. **Diagrammes de Séquence des Processus Critiques**
6. **Algorithmes & Logique d'Intelligence Opérationnelle**
7. **Guide de Démonstration Pas-à-Pas pour la Réunion Client**

---

## 1. Contexte & Enjeux Stratégiques

### 1.1 La Problématique Actuelle
Dans les structures hospitalières à fort trafic comme le **CHU du Point G**, la gestion manuelle ou archaïque des flux patients entraîne des goulets d'étranglement critiques :
- **Attente physique pénible :** Les patients attendent debout ou entassés dans des couloirs sans visibilité sur leur heure de passage.
- **Tensions et stress :** Incompréhension face à l'ordre de passage, sentiment d'injustice, risque de disputes.
- **Ressources médicales mal équilibrées :** Certains services sont débordés (ex: Cardiologie ou Caisse), tandis que d'autres guichets disposent de capacités sous-exploitées.
- **Absence de traçabilité :** Impossibilité d'évaluer le temps moyen de prise en charge ni d'identifier les pics d'affluence.

### 1.2 La Solution Apportée
L'application **Système Intelligent de Gestion des Files d'Attente du Point G** transforme radicalement l'expérience hospitalière :
1. **Délivrance de tickets virtuels & physiques** avec numéro unique et catégorisation (Normal vs Prioritaire).
2. **Information en temps réel :** Affichage du rang dans la file et estimation dynamique du temps d'attente restant.
3. **Appels vocaux automatisés** (Text-to-Speech) et moniteur central d'affichage pour une accessibilité universelle.
4. **Transfert direct inter-services :** Un médecin peut orienter instantanément son patient vers la radiologie ou le laboratoire sans refaire la queue à l'accueil.
5. **Module de Régulation IA :** Détection proactive des surcharges ($\ge 40$ min d'attente) avec proposition de réallocation automatique des guichets inactifs ou sous-chargés.

---

## 2. Architecture des Rôles : « Qui fait quoi ? »

L'application repose sur une séparation stricte des responsabilités à travers **5 profils d'utilisateurs** :

```
                               ┌────────────────────────────────┐
                               │   Système Hôpital du Point G   │
                               └───────────────┬────────────────┘
         ┌───────────────────┬─────────────────┼─────────────────┬──────────────────┐
         │                   │                 │                 │                  │
         ▼                   ▼                 ▼                 ▼                  ▼
    👤 Patient          🏢 Accueil        🩺 Médical        📊 Responsable     ⚙️ Administrateur
```

### 👤 Profil 1 : Le Patient (Usager)
*Exemple de compte démo : `patient@pointg.ml` / Nom : Amadou Touré*
- **Prise de ticket virtuel :** Sélectionne son service de consultation souhaité depuis son smartphone.
- **Suivi dynamique de son statut :** Consulte son rang exact dans la file et le temps d'attente estimé en minutes.
- **Alerte de convocation :** Est notifié visuellement lorsque son ticket est appelé au guichet correspondant.
- **Autonomie :** Peut annuler son ticket en cas d'empêchement, libérant automatiquement sa place pour les autres.

### 🏢 Profil 2 : L'Agent d'Accueil (Guichetier d'Orientation)
*Exemple de compte démo : `accueil@pointg.ml` / Nom : Fatoumata Traoré*
- **Inclusion numérique :** Émet des tickets pour les usagers ne disposant pas de smartphone ou ayant besoin d'assistance.
- **Saisie du dossier patient :** Renseigne le numéro de dossier médical pour assurer la continuité des soins.
- **Gestion des priorités :** Qualifie l'urgence du patient (**Normal** ou **Prioritaire** pour les urgences relatives, personnes âgées, femmes enceintes, nourrissons).

### 🩺 Profil 3 : Le Personnel Médical & Soignants (Médecins, Infirmiers, Laborantins)
*Exemple de compte démo : `medecin@pointg.ml` / Nom : Dr. Diallo*
- **Affectation de poste :** Sélectionne le guichet / bureau physique dans lequel il exerce (ex: *Poste 1 - Médecine*).
- **Appel du patient suivant :** Déclenche l'appel selon un algorithme qui privilégie d'abord les urgences, puis l'ordre chronologique d'arrivée (FIFO).
- **Annonce vocale synchronisée :** L'appel active le diffuseur sonore hospitalier ("*Ticket CONS-001 demandé au Poste 1*").
- **Cycle de consultation :**
  - Marque le patient comme **"En consultation"** au moment de son entrée dans le cabinet.
  - Marque le patient comme **"Terminé"** à la fin de la séance.
  - Signale le patient comme **"Absent"** s'il ne s'est pas présenté.
- **Transfert inter-services :** Réoriente le patient vers un autre service (ex: Radiologie) en générant automatiquement son nouveau ticket sans repasser par l'accueil.

### 📊 Profil 4 : Le Responsable de Service (Cadre de Santé / Superviseur)
*Exemple de compte démo : `responsable@pointg.ml` / Nom : Chef de Service*
- **Tableau de bord de pilotage :** Vision globale sur le flux des patients par service, le taux d'occupation et l'attente moyenne.
- **Gestion capacitaire :** Active ou désactive les guichets de son service selon les effectifs présents.
- **Réaffectation manuelle :** Bascule un guichet d'un service fluide vers un service engorgé pour absorber un pic d'affluence.

### ⚙️ Profil 5 : L'Administrateur & Moteur IA
*Exemple de compte démo : `admin@pointg.ml` / Nom : Administrateur*
- **Moteur d'arbitrage IA :** Reçoit des alertes intelligentes en cas de surcharge ($\ge 40$ min) ou de service sans guichet ouvert.
- **Application 1-Clic de l'IA :** Valide la proposition de l'algorithme qui active automatiquement un poste libre ou transfère un poste d'un service peu sollicité.
- **Gouvernance des accès :** Modifie à la volée le rôle de n'importe quel collaborateur.
- **Simulateur de flux temps réel :** Simule les arrivées de patients, les consultations terminées et les appels pour tester la résilience du système.
- **Réinitialisation du système :** Remet la base de données à blanc ou recharge les scénarios de démonstration.

---

## 3. Modèle de Données & Diagramme de Classes UML

Le système est architecturé autour de 4 entités relationnelles maîtresses :

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role : patient | agent_accueil | personnel_medical | responsable | admin
        +datetime email_verified_at
        +timestamps()
    }

    class Service {
        +int id
        +string nom
        +string code
        +string description
        +int temps_moyen_traitement
        +string statut : normal | surcharge
        +string icon
        +timestamps()
        +tickets() : HasMany~Ticket~
        +desks() : HasMany~Desk~
    }

    class Desk {
        +int id
        +string name
        +boolean active
        +int service_id
        +timestamps()
        +service() : BelongsTo~Service~
        +tickets() : HasMany~Ticket~
        +currentTicket() : HasOne~Ticket~
    }

    class Ticket {
        +int id
        +string numero
        +string patient_name
        +string patient_folder
        +int service_id
        +int desk_id
        +string status : en_attente | appele | en_cours | termine | absent | annule
        +string priority : normal | prioritaire
        +datetime called_at
        +datetime started_at
        +datetime finished_at
        +timestamps()
        +service() : BelongsTo~Service~
        +desk() : BelongsTo~Desk~
    }

    Service "1" --> "0..*" Desk : possède
    Service "1" --> "0..*" Ticket : contient
    Desk "0..1" --> "0..*" Ticket : traite
    Desk "1" ..> "0..1" Ticket : ticket en cours
```

### Dictionnaire des Données Clés

| Entité | Champ | Rôle Métier |
| :--- | :--- | :--- |
| **`Service`** | `temps_moyen_traitement` | Durée moyenne théorique d'une consultation (en minutes), base du calcul prédictif. |
| **`Service`** | `statut` | Évalué dynamiquement (`normal` si $< 40$ min, `surcharge` si $\ge 40$ min). |
| **`Desk`** | `active` | Booléen indiquant si le bureau/guichet est ouvert au public. |
| **`Desk`** | `service_id` | Service auquel le guichet est actuellement rattaché (modifiable à chaud). |
| **`Ticket`** | `priority` | `normal` ou `prioritaire`. Détermine l'ordre de passage lors de l'appel. |
| **`Ticket`** | `status` | Trace le parcours : `en_attente` ➔ `appele` ➔ `en_cours` ➔ `termine` (ou `absent`/`annule`). |
| **`Ticket`** | Horodatages | `called_at`, `started_at`, `finished_at` garantissent le calcul exact des KPIs de performance. |

---

## 4. Diagramme des Cas d'Utilisation UML (Use Cases)

```mermaid
flowchart LR
    subgraph Acteurs
        P([👤 Patient])
        A([🏢 Agent d'Accueil])
        M([🩺 Personnel Médical])
        R([📊 Responsable de Service])
        AD([⚙️ Administrateur])
        IA([🤖 Moteur de Régulation IA])
    end

    subgraph "Système de Gestion des Files d'Attente - CHU Point G"
        UC_Take([Prendre un ticket virtuel])
        UC_Track([Consulter son rang et attente estimée])
        UC_Cancel([Annuler son ticket])
        UC_DeskTake([Créer ticket guichet + Urgence])
        UC_Call([Appeler le patient suivant])
        UC_Vocal([Déclencher appel vocal TTS])
        UC_Status([Mettre à jour statut : En cours / Terminé])
        UC_Transfer([Transférer vers un autre service])
        UC_ConfigDesk([Ouvrir / Fermer / Réaffecter un guichet])
        UC_Stats([Consulter les KPIs d'attente])
        UC_Detect([Détecter les goulots de saturation])
        UC_ApplyIA([Appliquer la recommandation IA])
        UC_Simulate([Lancer un pas de simulation de flux])
        UC_Users([Gérer les rôles et habilitations])
    end

    P --> UC_Take
    P --> UC_Track
    P --> UC_Cancel

    A --> UC_Take
    A --> UC_DeskTake

    M --> UC_Call
    UC_Call -.->|include| UC_Vocal
    M --> UC_Status
    M --> UC_Transfer

    R --> UC_ConfigDesk
    R --> UC_Stats

    IA --> UC_Detect

    AD --> UC_ApplyIA
    AD --> UC_Simulate
    AD --> UC_Users
    AD --> UC_ConfigDesk
    AD --> UC_Stats
```

---

## 5. Diagrammes de Séquence des Processus Critiques

### Séquence 1 : Parcours d'Émission et de Suivi d'un Ticket
*Montre l'interaction entre le patient (ou l'accueil), le contrôleur et la mise à jour des métriques.*

```mermaid
sequenceDiagram
    autonumber
    actor Patient as 👤 Patient / Accueil
    participant Web as 🖥️ Interface Utilisateur
    participant Ctrl as ⚙️ QueueController
    participant Srv as 🏥 Modèle Service
    participant DB as 🗄️ Base de Données

    Patient->>Web: Sélectionne "Cardiologie", saisit son nom et choisit "Normal"
    Web->>Ctrl: POST /ticket/take {patient_name, service_id=1, priority='normal'}
    Ctrl->>Srv: Recherche code de service ('CARD')
    Ctrl->>DB: Compte tickets existants du jour pour générer le code
    Ctrl->>DB: INSERT INTO tickets (numero='CARD-004', status='en_attente', ...)
    DB-->>Ctrl: Ticket sauvegardé (ID: 4)
    Ctrl->>Ctrl: Calcule le rang dans la file (Position = 3)
    Ctrl->>Ctrl: Calcule l'attente : (3 × 20 min) / 1 guichet = 60 min
    Ctrl-->>Web: Redirection avec identifiant ticket en session
    Web-->>Patient: Affichage de la carte de suivi : "Ticket CARD-004 | Rang 3 | ~60 min"
```

---

### Séquence 2 : Appel Médecin, Synthèse Vocale et Transfert
*Illustre le travail du soignant, le déclenchement sonore et la réorientation.*

```mermaid
sequenceDiagram
    autonumber
    actor Medecin as 🩺 Dr. Diallo (Poste 1)
    participant Web as 🖥️ Dashboard Soignant
    participant Ctrl as ⚙️ QueueController
    participant DB as 🗄️ Base de Données
    participant TTS as 🔊 Synthèse Vocale (Web Speech)

    Medecin->>Web: Clique sur "Appeler le patient suivant"
    Web->>Ctrl: POST /desk/1/call
    Ctrl->>DB: SELECT * FROM tickets WHERE service_id=1 AND status='en_attente'<br/>ORDER BY priority DESC, created_at ASC LIMIT 1
    DB-->>Ctrl: Retourne Ticket CARD-004 (Prioritaire s'il existe, sinon FIFO)
    Ctrl->>DB: UPDATE tickets SET status='appele', called_at=NOW, desk_id=1
    Ctrl-->>Web: Flash session 'voice_call' {code: 'CARD-004', desk: 'Poste 1'}
    Web->>TTS: Synthèse audio : "Ticket C-A-R-D 0-0-4 au Poste 1"
    
    Note over Medecin,Web: Le patient arrive au cabinet
    Medecin->>Web: Clique sur "Prendre en charge"
    Web->>Ctrl: POST /ticket/4/status/en_cours
    Ctrl->>DB: UPDATE tickets SET status='en_cours', started_at=NOW
    
    Note over Medecin,Web: Examen terminé. Besoin d'une radio thoracique.
    Medecin->>Web: Clique sur "Transférer vers Radiologie"
    Web->>Ctrl: POST /ticket/4/transfer {target_service_id=3, desk_id=1}
    Ctrl->>DB: UPDATE tickets SET status='termine', finished_at=NOW (CARD-004)
    Ctrl->>DB: INSERT INTO tickets (numero='RADIO-005', service_id=3, status='en_attente')
    Ctrl-->>Web: Notification "Patient transféré vers Radiologie (RADIO-005)"
```

---

### Séquence 3 : Surveillance Intelligente et Régulation Automatique (IA)
*Démontre la valeur ajoutée technologique du système d'équilibrage de charge.*

```mermaid
sequenceDiagram
    autonumber
    participant Engine as ⚙️ Moteur d'Analyse Métrique
    participant DB as 🗄️ Base de Données
    participant IA as 🤖 Algorithme IA
    actor Admin as ⚙️ Administrateur / Superviseur
    participant Web as 🖥️ Interface d'Alerte

    Engine->>DB: Évalue la file : 5 patients en attente en Cardiologie
    Engine->>Engine: Calcul : (5 × 20 min) / 1 guichet = 100 min d'attente
    Engine->>IA: Alerte : Statut = 'SURCHARGE' (> seuil 40 min)
    IA->>DB: Recherche un guichet inactif (ex: Poste 6 - Inactif)
    IA-->>Web: Génère Recommandation : "Surcharge Cardiologie (100 min) - Ouvrir Poste 6"
    Web-->>Admin: Affichage d'une bannière d'alerte avec bouton d'action directe
    Admin->>Web: Clique sur "Ouvrir Poste 6 pour ce service"
    Web->>Engine: GET /ia/recommendation/resolve_surcharge/1
    Engine->>DB: UPDATE desks SET active=true, service_id=1 WHERE id=6
    Engine->>Engine: Nouveau calcul : (5 × 20 min) / 2 guichets = 50 min (-50% d'attente !)
    Engine-->>Web: Notification : "Poste 6 activé. Temps divisé par deux."
```

---

## 6. Algorithmes & Logique d'Intelligence Opérationnelle

### 6.1 Calcul Dynamique du Temps d'Attente
Contrairement aux systèmes basiques à temps fixe, l'application recalcule le temps d'attente estimé pour chaque service en temps réel :

$$\text{Temps Estimé (min)} = \frac{\text{Nombre de Tickets en Attente} \times \text{Temps Moyen par Acte}}{\text{Nombre de Guichets Actifs pour ce Service}}$$

*Si aucun guichet n'est ouvert, le diviseur est temporairement fixé à 1 pour afficher l'arriéré tout en déclenchant immédiatement une alerte IA.*

### 6.2 Priorisation Éthique et Médicale (Triage)
L'ordre de distribution des tickets lors de l'appel soignant respecte la règle :
```sql
ORDER BY 
    CASE WHEN priority = 'prioritaire' THEN 0 ELSE 1 END,
    created_at ASC
```
1. Tous les cas prioritaires enregistrés passent avant les cas normaux.
2. Au sein d'une même catégorie de priorité, la stricte règle du premier arrivé, premier servi (FIFO) s'applique.

### 6.3 Moteur de Décision IA
Le système évalue en permanence deux conditions d'alerte :
1. **Condition de Surcharge :** $\text{Temps Estimé} \ge 40\text{ minutes}$.
   - *Action IA suggérée :* Activer en priorité un guichet fermé disponible. À défaut, réaffecter un guichet assigné à un service ayant moins de 10 minutes d'attente.
2. **Condition de Rupture :** $\text{Patients en attente} > 0$ ET $\text{Guichets Actifs} = 0$.
   - *Action IA suggérée :* Ouvrir immédiatement un guichet pour ce service.

---

## 7. Guide de Démonstration Pas-à-Pas pour la Réunion Client

Pour impressionner votre auditoire lors de votre présentation de ce soir, suivez ce déroulé chronométré :

### Étape 1 : Introduction & Constat (2 minutes)
- Présentez le défi du Point G : des files physiques désorganisées, des soignants stressés et des temps d'attente inconnus.
- Ouvrez l'application sur [http://127.0.0.1:8000](http://127.0.0.1:8000).
- Montrez la page d'accueil et le tableau de bord avec son design moderne, clair et dynamique.

### Étape 2 : Démonstration Côté Patient (2 minutes)
- Connectez-vous ou basculez sur le profil **👤 Patient**.
- Montrez comment prendre un ticket en 2 clics pour la **Consultation Générale**.
- Montrez la carte dynamique du ticket : **le numéro généré, la position dans la file et les minutes restantes estimées**.

### Étape 3 : Démonstration Côté Médecin & Appel Vocal (3 minutes)
- Basculez sur le profil **🩺 Personnel Médical** (Dr. Diallo).
- Choisissez le guichet (ex: *Poste 1*).
- Cliquez sur **« Appeler le patient suivant »**.
- **Effet garanti :** Faites écouter l'annonce vocale automatique qui retentit dans les haut-parleurs.
- Montrez le passage à l'état *"En consultation"*, puis effectuez un **« Transfert vers Radiologie »** pour illustrer la continuité de soins sans retour à l'accueil.

### Étape 4 : Démonstration du Moteur IA & Pilotage (3 minutes)
- Basculez sur le profil **⚙️ Admin & IA**.
- Cliquez une ou deux fois sur le bouton **« Simuler une étape »** pour simuler l'arrivée soudaine de nouveaux patients.
- Observez l'apparition de l'alerte orange **« Surcharge détectée »**.
- Cliquez sur le bouton **« Appliquer la recommandation »** : montrez au client comment le guichet inactif s'allume automatiquement et comment le temps d'attente chute immédiatement de moitié.

### Récapitulatif des Accès Démo :

| Rôle à Déclarer | Identifiant Démo | Mot de passe |
| :--- | :--- | :--- |
| **Administrateur / Démonstrateur** | `admin@pointg.ml` | `password` |
| **Médecin Consultation** | `medecin@pointg.ml` | `password` |
| **Agent d'Accueil** | `accueil@pointg.ml` | `password` |
| **Patient Type** | `patient@pointg.ml` | `password` |
| **Cadre de Santé** | `responsable@pointg.ml` | `password` |

---
*Document élaboré pour la soutenance et la présentation commerciale du CHU du Point G.*
