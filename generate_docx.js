import { Document, Packer, Paragraph, TextRun, HeadingLevel, Table, TableRow, TableCell, BorderStyle, WidthType, AlignmentType, ShadingType } from "docx";
import fs from "fs";
import path from "path";
import { execSync } from "child_process";

// ----------------------------------------------------
// 1. GENERATE WORD (.DOCX) FILE
// ----------------------------------------------------
console.log("Generating Word (.docx) document...");

const PRIMARY_COLOR = "0D9488"; // Teal/Medical
const DARK_COLOR = "1E293B";    // Slate 800
const LIGHT_BG = "F8FAFC";      // Slate 50
const BORDER_COLOR = "CBD5E1";  // Slate 300
const ACCENT_COLOR = "2563EB";  // Blue

const doc = new Document({
  styles: {
    default: {
      document: {
        run: {
          font: "Calibri",
          size: 22, // 11pt
          color: "334155",
        },
        paragraph: {
          spacing: { line: 280, after: 140 },
        },
      },
    },
  },
  sections: [
    {
      properties: {
        page: {
          margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 }, // 1 inch
        },
      },
      children: [
        // COVER PAGE HEADER
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { before: 2000, after: 200 },
          children: [
            new TextRun({
              text: "🏥 CENTRE HOSPITALIER UNIVERSITAIRE DU POINT G",
              bold: true,
              size: 26,
              color: PRIMARY_COLOR,
            }),
          ],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 600 },
          children: [
            new TextRun({
              text: "Bamako — République du Mali",
              italics: true,
              size: 22,
              color: "64748B",
            }),
          ],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 200 },
          children: [
            new TextRun({
              text: "DOSSIER DE PRÉSENTATION & SPÉCIFICATIONS TECHNIQUES",
              bold: true,
              size: 36,
              color: DARK_COLOR,
            }),
          ],
        }),
        new Paragraph({
          alignment: AlignmentType.CENTER,
          spacing: { after: 1200 },
          children: [
            new TextRun({
              text: "Système Intelligent de Gestion des Files d'Attente et des Flux Patients",
              size: 28,
              color: PRIMARY_COLOR,
              bold: true,
            }),
          ],
        }),

        // METADATA BOX
        new Table({
          width: { size: 100, type: WidthType.PERCENTAGE },
          rows: [
            new TableRow({
              children: [
                new TableCell({
                  shading: { type: ShadingType.CLEAR, fill: LIGHT_BG },
                  margins: { top: 200, bottom: 200, left: 300, right: 300 },
                  children: [
                    new Paragraph({
                      children: [
                        new TextRun({ text: "Type de document : ", bold: true }),
                        new TextRun({ text: "Dossier Technique & Commercial de Soutenance\n" }),
                        new TextRun({ text: "Cible : ", bold: true }),
                        new TextRun({ text: "Direction de l'Hôpital, Cadres Médicaux & Clients\n" }),
                        new TextRun({ text: "Version : ", bold: true }),
                        new TextRun({ text: "1.0 (Validé pour démonstration live)\n" }),
                        new TextRun({ text: "Date : ", bold: true }),
                        new TextRun({ text: "Septembre 2026\n" }),
                        new TextRun({ text: "Environnement de test : ", bold: true }),
                        new TextRun({ text: "http://127.0.0.1:8000" }),
                      ],
                    }),
                  ],
                }),
              ],
            }),
          ],
        }),

        new Paragraph({
          pageBreakBefore: true,
          text: "1. Contexte & Enjeux Stratégiques",
          heading: HeadingLevel.HEADING_1,
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "Dans les grands centres hospitaliers d'Afrique de l'Ouest comme le CHU du Point G, la gestion traditionnelle des files d'attente crée des situations critiques :",
            }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Attente physique prolongée et anxiogène : ", bold: true }),
            new TextRun({ text: "Les usagers patientent des heures sans visibilité sur leur ordre de passage." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Tensions aux guichets : ", bold: true }),
            new TextRun({ text: "L'opacité engendre des réclamations et perturbe la concentration des agents." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Déséquilibre des charges : ", bold: true }),
            new TextRun({ text: "Certains services sont submergés (ex: Cardiologie, Caisse) tandis que des postes voisins sont inactifs." }),
          ],
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "La solution déployée digitalise intégralement le parcours du patient grâce à une régulation intelligente, des annonces sonores automatisées et une estimation dynamique des temps d'attente.",
            }),
          ],
        }),

        new Paragraph({
          pageBreakBefore: true,
          text: "2. Architecture des Rôles (« Qui fait quoi ? »)",
          heading: HeadingLevel.HEADING_1,
        }),
        new Paragraph({
          children: [
            new TextRun({
              text: "L'application sépare strictement les prérogatives en 5 profils d'utilisateurs :",
            }),
          ],
        }),

        // ROLES TABLE
        new Table({
          width: { size: 100, type: WidthType.PERCENTAGE },
          rows: [
            new TableRow({
              tableHeader: true,
              children: [
                new TableCell({
                  shading: { type: ShadingType.CLEAR, fill: PRIMARY_COLOR },
                  children: [new Paragraph({ children: [new TextRun({ text: "Rôle", bold: true, color: "FFFFFF" })] })],
                }),
                new TableCell({
                  shading: { type: ShadingType.CLEAR, fill: PRIMARY_COLOR },
                  children: [new Paragraph({ children: [new TextRun({ text: "Acteur Type", bold: true, color: "FFFFFF" })] })],
                }),
                new TableCell({
                  shading: { type: ShadingType.CLEAR, fill: PRIMARY_COLOR },
                  children: [new Paragraph({ children: [new TextRun({ text: "Missions & Fonctionnalités Clés", bold: true, color: "FFFFFF" })] })],
                }),
              ],
            }),
            new TableRow({
              children: [
                new TableCell({ children: [new Paragraph({ text: "👤 Patient" })] }),
                new TableCell({ children: [new Paragraph({ text: "Amadou Touré (patient@pointg.ml)" })] }),
                new TableCell({ children: [new Paragraph({ text: "Prend un ticket virtuel sur smartphone, suit son rang et l'attente estimée en temps réel, reçoit l'alerte d'appel." })] }),
              ],
            }),
            new TableRow({
              children: [
                new TableCell({ children: [new Paragraph({ text: "🏢 Agent Accueil" })] }),
                new TableCell({ children: [new Paragraph({ text: "Fatoumata Traoré (accueil@pointg.ml)" })] }),
                new TableCell({ children: [new Paragraph({ text: "Émet les tickets pour les usagers sans téléphone, enregistre le numéro de dossier, qualifie la priorité (Normal / Prioritaire)." })] }),
              ],
            }),
            new TableRow({
              children: [
                new TableCell({ children: [new Paragraph({ text: "🩺 Personnel Médical" })] }),
                new TableCell({ children: [new Paragraph({ text: "Dr. Diallo (medecin@pointg.ml)" })] }),
                new TableCell({ children: [new Paragraph({ text: "Sélectionne son bureau, clique sur 'Appeler le suivant' (déclenche la voix TTS), gère le cycle consultation, transfère vers un autre service." })] }),
              ],
            }),
            new TableRow({
              children: [
                new TableCell({ children: [new Paragraph({ text: "📊 Responsable" })] }),
                new TableCell({ children: [new Paragraph({ text: "Chef de Service (responsable@pointg.ml)" })] }),
                new TableCell({ children: [new Paragraph({ text: "Supervise les flux et KPIs en direct, ouvre/ferme les guichets, réaffecte manuellement les ressources selon la pression." })] }),
              ],
            }),
            new TableRow({
              children: [
                new TableCell({ children: [new Paragraph({ text: "⚙️ Admin & IA" })] }),
                new TableCell({ children: [new Paragraph({ text: "Administrateur (admin@pointg.ml)" })] }),
                new TableCell({ children: [new Paragraph({ text: "Valide les alertes et recommandations IA en 1 clic (désengorgement), simule les flux, gère les rôles utilisateurs et la maintenance." })] }),
              ],
            }),
          ],
        }),

        new Paragraph({
          pageBreakBefore: true,
          text: "3. Modèle de Données & Classes",
          heading: HeadingLevel.HEADING_1,
        }),
        new Paragraph({
          children: [
            new TextRun({ text: "Le système repose sur 4 classes Eloquent fondamentales :\n" }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Service : ", bold: true }),
            new TextRun({ text: "Représente une unité médicale (Cardiologie, Radio, Labo...). Définit la durée moyenne par consultation (temps_moyen_traitement) et le statut calculé (normal / surcharge)." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Desk (Guichet/Poste) : ", bold: true }),
            new TextRun({ text: "Poste physique de travail rattaché à un service. Dispose d'un état actif/inactif et d'un lien vers le ticket en cours d'examen." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Ticket : ", bold: true }),
            new TextRun({ text: "Entité centrale du flux patient. Comprend le numéro unique, la priorité (normal/prioritaire), les horodatages de traçabilité (called_at, started_at, finished_at) et le statut (en_attente, appele, en_cours, termine, absent, annule)." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "User : ", bold: true }),
            new TextRun({ text: "Gestion de l'authentification et des 5 rôles avec contrôle d'accès strict (RBAC)." }),
          ],
        }),

        new Paragraph({
          pageBreakBefore: true,
          text: "4. Algorithmes & Intelligence Opérationnelle",
          heading: HeadingLevel.HEADING_1,
        }),
        new Paragraph({
          children: [
            new TextRun({ text: "A. Calcul Dynamique du Temps d'Attente :\n", bold: true }),
            new TextRun({ text: "Formule : Temps Estimé (min) = (Nombre de Tickets en Attente × Temps Moyen par Consultation) / Guichets Actifs.\n" }),
            new TextRun({ text: "B. Triage Médical & Ordre d'Appel :\n", bold: true }),
            new TextRun({ text: "Lors de l'appel par le médecin, l'algorithme SQL sélectionne : ORDER BY (CASE WHEN priority = 'prioritaire' THEN 0 ELSE 1 END), created_at ASC. Les cas prioritaires passent obligatoirement avant les cas normaux, quel que soit leur ordre d'arrivée.\n" }),
            new TextRun({ text: "C. Moteur de Régulation IA :\n", bold: true }),
            new TextRun({ text: "Dès que le temps d'attente calculé dépasse 40 minutes, une alerte est déclenchée. L'algorithme recherche un guichet fermé ou un guichet d'un service fluide (< 10 min d'attente) et propose en 1 clic sa réaffectation pour diviser le temps d'attente par deux." }),
          ],
        }),

        new Paragraph({
          pageBreakBefore: true,
          text: "5. Guide de Démonstration Pas-à-Pas pour la Réunion Client",
          heading: HeadingLevel.HEADING_1,
        }),
        new Paragraph({
          children: [
            new TextRun({ text: "Pour votre présentation de ce soir, voici le plan d'action infaillible en 4 étapes :\n" }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Étape 1 (Introduction) : ", bold: true }),
            new TextRun({ text: "Ouvrir http://127.0.0.1:8000. Présenter le design épuré, le moniteur d'appel centralisé et le tableau de bord des services." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Étape 2 (Parcours Patient) : ", bold: true }),
            new TextRun({ text: "Prendre un ticket virtuel pour la Consultation Générale. Montrer le numéro généré et le calcul en temps réel du rang et des minutes d'attente." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Étape 3 (Poste Médical & Voix) : ", bold: true }),
            new TextRun({ text: "Se connecter en Médecin, choisir Poste 1, cliquer sur 'Appeler le suivant'. Faire écouter la synthèse vocale automatique. Cliquer sur 'Transférer vers Radiologie' pour prouver la fluidité du parcours de soins." }),
          ],
        }),
        new Paragraph({
          bullet: { level: 0 },
          children: [
            new TextRun({ text: "Étape 4 (Moteur IA) : ", bold: true }),
            new TextRun({ text: "Basculez sur l'espace Admin. Cliquer sur 'Simuler une étape' pour générer un flux de patients. Dès que l'alerte orange de surcharge s'affiche, cliquer sur 'Appliquer la recommandation IA' pour montrer la chute instantanée du temps d'attente." }),
          ],
        }),

        new Paragraph({
          spacing: { before: 400 },
          children: [
            new TextRun({ text: "Identifiants de connexion pré-configurés (Mot de passe universel : password) :\n", bold: true }),
            new TextRun({ text: "• Admin : admin@pointg.ml\n• Médecin : medecin@pointg.ml\n• Accueil : accueil@pointg.ml\n• Responsable : responsable@pointg.ml\n• Patient : patient@pointg.ml\n" }),
          ],
        }),
      ],
    },
  ],
});

const docxBuffer = await Packer.toBuffer(doc);
const docxPath = path.resolve("c:/Users/kalandew43/.gemini/antigravity-ide/scratch/gestion-tickets-point-g/DOSSIER_PRESENTATION_CLIENT.docx");
fs.writeFileSync(docxPath, docxBuffer);
console.log("✅ Word file created:", docxPath);
