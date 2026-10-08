<?php

namespace App\Filament\Resources\RecoursResource\Pages;

use App\Filament\Resources\RecoursResource;
use App\Models\Recours;
use App\Models\RecoursJuryReview;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewRecours extends ViewRecord
{
    protected static string $resource = RecoursResource::class;

    protected function resolveRecord(int|string $key): Model
    {
        return Recours::query()
            ->with([
                'academicYear',
                'promotion',
                'courses',
                'reasons',
                'attachments',
                'juryReview',
                'juryReview.juryMember',
            ])
            ->findOrFail($key);
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIONS
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('traiterJury')
                ->label('Traiter le recours')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')

                ->modalHeading('Traitement du recours par le jury')

                ->modalDescription(
                    'Complétez les informations du jury puis enregistrez la décision.'
                )

                ->modalSubmitActionLabel('Enregistrer')

                /*
                 * ----------------------------------------------------------
                 * Pré-remplissage du formulaire
                 * ----------------------------------------------------------
                 */

                ->fillForm(function (): array {
                    $jury = $this->record->juryReview;

                    return [
                        'jury_member_id' => $jury?->jury_member_id,

                        'case_presentation' =>
                            $jury?->case_presentation,

                        'professor_appreciation' =>
                            $jury?->professor_appreciation,

                        'material_evidence' =>
                            $jury?->material_evidence,

                        'decision' =>
                            $jury?->decision ?? 'pending',

                        'decision_comment' =>
                            $jury?->decision_comment,

                        'decided_at' =>
                            $jury?->decided_at,
                    ];
                })

                /*
                 * ----------------------------------------------------------
                 * FORMULAIRE DU JURY
                 * ----------------------------------------------------------
                 */

                ->form([

                    Select::make('jury_member_id')
                        ->label('Membre du jury')

                        /*
                         * On utilise options() au lieu de relationship().
                         * Cela évite l'erreur getResults() sur null.
                         */
                        ->options(function (): array {
                            return User::query()
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                ->get()
                                ->mapWithKeys(
                                    fn (User $user) => [
                                        $user->id => trim(
                                            $user->first_name
                                            . ' '
                                            . $user->last_name
                                        ),
                                    ]
                                )
                                ->toArray();
                        })

                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Textarea::make('case_presentation')
                        ->label('Présentation du cas')
                        ->rows(4)
                        ->columnSpanFull(),

                    Textarea::make('professor_appreciation')
                        ->label('Appréciation du professeur')
                        ->rows(4)
                        ->columnSpanFull(),

                    Textarea::make('material_evidence')
                        ->label('Éléments matériels')
                        ->rows(4)
                        ->columnSpanFull(),

                    Select::make('decision')
                        ->label('Décision du jury')
                        ->options([
                            'pending' => 'En attente',
                            'founded' => 'Fondé',
                            'unfounded' => 'Non fondé',
                        ])
                        ->required()
                        ->live(),

                    DateTimePicker::make('decided_at')
                        ->label('Date de décision')
                        ->seconds(false)
                        ->nullable()
                        ->visible(
                            fn (Get $get): bool =>
                                in_array(
                                    $get('decision'),
                                    ['founded', 'unfounded'],
                                    true
                                )
                        ),

                    Textarea::make('decision_comment')
                        ->label('Commentaire de la décision')
                        ->rows(4)
                        ->columnSpanFull(),
                ])

                /*
                 * ----------------------------------------------------------
                 * SAUVEGARDE
                 * ----------------------------------------------------------
                 */

                ->action(function (array $data): void {

                    /*
                     * Si une décision définitive est prise
                     * sans date, on met automatiquement la date actuelle.
                     */
                    if (
                        in_array(
                            $data['decision'] ?? null,
                            ['founded', 'unfounded'],
                            true
                        )
                        && empty($data['decided_at'])
                    ) {
                        $data['decided_at'] = now();
                    }

                    /*
                     * Si le jury remet le recours "En attente",
                     * aucune date de décision ne doit être conservée.
                     */
                    if (
                        ($data['decision'] ?? null) === 'pending'
                    ) {
                        $data['decided_at'] = null;
                    }

                    /*
                     * Création ou modification du traitement du jury.
                     */
                    $jury = RecoursJuryReview::updateOrCreate(
                        [
                            'recours_id' => $this->record->id,
                        ],
                        [
                            'jury_member_id' =>
                                $data['jury_member_id'] ?? null,

                            'case_presentation' =>
                                $data['case_presentation'] ?? null,

                            'professor_appreciation' =>
                                $data['professor_appreciation'] ?? null,

                            'material_evidence' =>
                                $data['material_evidence'] ?? null,

                            'decision' =>
                                $data['decision'],

                            'decision_comment' =>
                                $data['decision_comment'] ?? null,

                            'decided_at' =>
                                $data['decided_at'] ?? null,
                        ]
                    );

                    /*
                     * Mise à jour du statut du recours.
                     */
                    if (
                        in_array(
                            $jury->decision,
                            ['founded', 'unfounded'],
                            true
                        )
                    ) {
                        $this->record->update([
                            'status' => 'decided',
                        ]);
                    } else {
                        $this->record->update([
                            'status' => 'under_review',
                        ]);
                    }

                    /*
                     * Rechargement du recours.
                     */
                    $this->record->refresh();

                    $this->record->load([
                        'academicYear',
                        'promotion',
                        'courses',
                        'reasons',
                        'attachments',
                        'juryReview',
                        'juryReview.juryMember',
                    ]);

                    Notification::make()
                        ->title('Traitement enregistré')
                        ->body(
                            'Les informations du jury ont été enregistrées avec succès.'
                        )
                        ->success()
                        ->send();
                }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FICHE DU RECOURS
    |--------------------------------------------------------------------------
    */

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | EN-TÊTE UCC
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->schema([

                        TextEntry::make('university')
                            ->label('')
                            ->default(
                                'UNIVERSITÉ CATHOLIQUE DU CONGO'
                            )
                            ->weight('bold')
                            ->size('lg')
                            ->alignCenter(),

                        TextEntry::make('faculty')
                            ->label('')
                            ->default(
                                'FACULTÉ DES SCIENCES INFORMATIQUES'
                            )
                            ->weight('bold')
                            ->size('md')
                            ->alignCenter(),

                        TextEntry::make('document_title')
                            ->label('')
                            ->default('FICHE DE RECOURS')
                            ->weight('bold')
                            ->size('lg')
                            ->alignCenter(),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | INFORMATIONS DU RECOURS
                |--------------------------------------------------------------------------
                */

                Section::make('INFORMATIONS DU RECOURS')
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextEntry::make('academicYear.name')
                                    ->label('Année académique'),

                                TextEntry::make('promotion.name')
                                    ->label('Promotion'),
                            ]),

                        Grid::make(2)
                            ->schema([

                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn (?string $state): string =>
                                            match ($state) {
                                                'draft' => 'Brouillon',
                                                'submitted' => 'Soumis',
                                                'under_review' => 'En examen',
                                                'decided' => 'Décidé',
                                                default => $state ?? '—',
                                            }
                                    )
                                    ->color(
                                        fn (?string $state): string =>
                                            match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'warning',
                                                'under_review' => 'info',
                                                'decided' => 'success',
                                                default => 'gray',
                                            }
                                    ),

                                TextEntry::make('submitted_at')
                                    ->label('Date de soumission')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | IDENTITÉ
                |--------------------------------------------------------------------------
                */

                Section::make(
                    'IDENTIFICATION DE L’ÉTUDIANT(E)'
                )
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextEntry::make('last_name')
                                    ->label('Nom'),

                                TextEntry::make('post_name')
                                    ->label('Post-nom')
                                    ->placeholder('—'),

                                TextEntry::make('first_name')
                                    ->label('Prénom'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | COURS
                |--------------------------------------------------------------------------
                */

                Section::make('COURS CONCERNÉ(S)')
                    ->schema([

                        TextEntry::make('courses_list')
                            ->label('')
                            ->state(
                                function (Recours $record): string {

                                    if ($record->courses->isEmpty()) {
                                        return 'Aucun cours renseigné.';
                                    }

                                    return $record->courses
                                        ->values()
                                        ->map(
                                            function ($course, $index) {

                                                return ($index + 1)
                                                    . '. '
                                                    . $course->title
                                                    . ' — Professeur : '
                                                    . $course->pivot
                                                        ->professor_name;
                                            }
                                        )
                                        ->implode("\n");
                                }
                            )
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | MOTIFS
                |--------------------------------------------------------------------------
                */

                Section::make('MOTIF(S) DU RECOURS')
                    ->schema([

                        TextEntry::make('reasons_list')
                            ->label('')
                            ->state(
                                function (Recours $record): string {

                                    if ($record->reasons->isEmpty()) {
                                        return 'Aucun motif renseigné.';
                                    }

                                    return $record->reasons
                                        ->values()
                                        ->map(
                                            function ($reason, $index) {

                                                $text =
                                                    ($index + 1)
                                                    . '. '
                                                    . $reason->name;

                                                if (
                                                    $reason
                                                        ->pivot
                                                        ->description
                                                ) {
                                                    $text .=
                                                        "\nDescription : "
                                                        . $reason
                                                            ->pivot
                                                            ->description;
                                                }

                                                return $text;
                                            }
                                        )
                                        ->implode("\n\n");
                                }
                            )
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | PIÈCES JUSTIFICATIVES
                |--------------------------------------------------------------------------
                */

                Section::make('PIÈCES JUSTIFICATIVES')
                    ->schema([

                        TextEntry::make('attachments_list')
                            ->label('')
                            ->state(
                                function (Recours $record): string {

                                    if ($record->attachments->isEmpty()) {
                                        return 'Aucune pièce justificative.';
                                    }

                                    return $record->attachments
                                        ->values()
                                        ->map(
                                            function ($attachment, $index) {

                                                $size = $attachment->size
                                                    ? number_format(
                                                        $attachment->size / 1024,
                                                        2
                                                    ) . ' Ko'
                                                    : 'Taille inconnue';

                                                return ($index + 1)
                                                    . '. '
                                                    . $attachment->original_name
                                                    . ' — '
                                                    . $size;
                                            }
                                        )
                                        ->implode("\n");
                                }
                            )
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | PARTIE JURY
                |--------------------------------------------------------------------------
                */

                Section::make('PARTIE RÉSERVÉE AU JURY')
                    ->description(
                        'Cette partie est réservée au traitement administratif du recours.'
                    )
                    ->schema([

                        Grid::make(2)
                            ->schema([

                                TextEntry::make('jury_member_name')
                                    ->label('Président du jury')
                                    ->state(
                                        function (
                                            Recours $record
                                        ): string {

                                            $member =
                                                $record
                                                    ->juryReview
                                                    ?->juryMember;

                                            if (! $member) {
                                                return 'Non désigné';
                                            }

                                            return trim(
                                                $member->first_name
                                                . ' '
                                                . $member->last_name
                                            );
                                        }
                                    ),

                                TextEntry::make('jury_decision')
                                    ->label('Décision actuelle')
                                    ->state(
                                        function (
                                            Recours $record
                                        ): string {

                                            return match (
                                                $record
                                                    ->juryReview
                                                    ?->decision
                                            ) {
                                                'pending' =>
                                                    'En attente',

                                                'founded' =>
                                                    'Fondé',

                                                'unfounded' =>
                                                    'Non fondé',

                                                default =>
                                                    'Non renseignée',
                                            };
                                        }
                                    )
                                    ->badge()
                                    ->color(
                                        function (
                                            Recours $record
                                        ): string {

                                            return match (
                                                $record
                                                    ->juryReview
                                                    ?->decision
                                            ) {
                                                'pending' =>
                                                    'warning',

                                                'founded' =>
                                                    'success',

                                                'unfounded' =>
                                                    'danger',

                                                default =>
                                                    'gray',
                                            };
                                        }
                                    ),
                            ]),

                        TextEntry::make(
                            'juryReview.case_presentation'
                        )
                            ->label('Présentation du cas')
                            ->placeholder('Non renseignée')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'juryReview.professor_appreciation'
                        )
                            ->label('Appréciation du professeur')
                            ->placeholder('Non renseignée')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'juryReview.material_evidence'
                        )
                            ->label('Éléments matériels')
                            ->placeholder('Non renseignés')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'juryReview.decision_comment'
                        )
                            ->label('Commentaire de la décision')
                            ->placeholder('Aucun commentaire')
                            ->columnSpanFull(),

                        TextEntry::make(
                            'juryReview.decided_at'
                        )
                            ->label('Date de décision')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Non décidée'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
