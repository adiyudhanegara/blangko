<x-filament-panels::page>
    <div class="space-y-6">

        <x-filament::section>
            <x-slot name="heading">{{ __('admin.how_to_use') }}</x-slot>

            <ol style="list-style: decimal; padding-left: 1.25rem; font-size: 0.875rem; color: rgb(107 114 128); display: flex; flex-direction: column; gap: 0.25rem;">
                <li>{!! __('admin.part_inst_1') !!}</li>
                <li>{!! __('admin.part_inst_2') !!}</li>
                <li>{!! __('admin.part_inst_3') !!}</li>
                <li>{!! __('admin.part_inst_4') !!}</li>
                <li>{!! __('admin.part_inst_5') !!}</li>
                <li>{!! __('admin.part_inst_6') !!}</li>
                <li>{!! __('admin.part_inst_7') !!}</li>
            </ol>

            <div style="margin-top: 1rem;">
                <a
                    href="{{ route('admin.participant-import-template') }}"
                    style="display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; font-weight: 500; color: rgb(234 179 8); text-decoration: none;"
                    onmouseover="this.style.opacity='0.8'"
                    onmouseout="this.style.opacity='1'"
                >
                    {{ __('admin.download_template') }}
                </a>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('admin.upload_completed_file') }}</x-slot>

            {{ $this->form }}

            @if (!empty($this->importErrors))
                <div style="margin-top: 1rem; border-radius: 0.5rem; border: 1px solid rgb(254 202 202); background: rgb(254 242 242); padding: 1rem;">
                    <p style="font-size: 0.875rem; font-weight: 600; color: rgb(153 27 27); margin-bottom: 0.5rem;">
                        {{ __('admin.import_warning_count', ['count' => count($this->importErrors)]) }}
                    </p>
                    <ul style="list-style: disc; padding-left: 1.25rem; display: flex; flex-direction: column; gap: 0.125rem; font-size: 0.875rem; color: rgb(185 28 28);">
                        @foreach ($this->importErrors as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="margin-top: 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem;">
                <a
                    href="{{ \App\Filament\Resources\ParticipantResource::getUrl('index') }}"
                    style="font-size: 0.875rem; color: rgb(107 114 128); text-decoration: none;"
                    onmouseover="this.style.color='rgb(55 65 81)'"
                    onmouseout="this.style.color='rgb(107 114 128)'"
                >
                    {{ __('admin.cancel') }}
                </a>

                <x-filament::button
                    wire:click="import"
                    wire:loading.attr="disabled"
                    wire:target="import"
                >
                    <span wire:loading.remove wire:target="import">{{ __('admin.import_participants_btn') }}</span>
                    <span wire:loading wire:target="import">{{ __('admin.importing') }}</span>
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
