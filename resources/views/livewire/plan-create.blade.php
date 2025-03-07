<!-- plan-create.blade.php -->

<div>
    <form wire:submit.prevent="submit">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="name_en">{{ __('app.name-en') }}</label>
                    <input type="text" class="form-control"  name="name_en"
                        value="{{ old('name_en') }}" id="name_en" wire:model='name_en'>
                    @error('name_en')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="name_er">{{ __('app.name-ar') }}</label>
                    <input type="text" class="form-control"  name="name_ar"
                        value="{{ old('name_ar') }}" id="name_ar" wire:model='name_ar'>
                    @error('name_ar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="validationTooltip03">{{ __('app.description-en') }}</label>
                    <div>
                        <textarea name="description_en" class="form-control" rows="3" id="validationTooltip03"
                             wire:model='description_en'>{{ old('description_en') }}</textarea>
                    </div>
                    @error('description_en')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="validationTooltip04">{{ __('app.description-ar') }}</label>
                    <div>
                        <textarea name="description_ar" class="form-control" rows="3" id="validationTooltip04"
                            wire:model='description_ar'>{{ old('description_ar') }}</textarea>
                    </div>
                    @error('description_ar')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12" style="text-align: end;">
                <button type="button" class="btn btn-success" wire:click="addDay">{{ __('app.plan.add-another-day') }}</button>
            </div>
        </div>
        @foreach ($days as $dayIndex => $day)
            <div class="d-flex justify-content-lg-between">
                <h5 class="mb-4">{{ __('app.plan.day-number') }} {{ $loop->iteration }}:</h5>
                @if ($dayIndex > 0)
                    <button type="button" class="btn btn-danger" wire:click="removeDay({{ $dayIndex }})">{{ __('app.plan.remove-this-day') }}</button>
                @endif
            </div>
            @foreach ($day['activities'] as $activityIndex => $activity)
                <div class="mb-3">
                    <h6 class="text-success mb-3">{{ __('app.plan.activity-number') }}{{ $loop->iteration }}:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label"
                                    for="validationTooltip05">{{ __('app.plan.activity-name-en') }}</label>
                                <input type="text" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][name_en]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.name_en">
                                @error("days.$dayIndex.activities.$activityIndex.name_en")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label"
                                    for="validationTooltip05">{{ __('app.plan.activity-name-ar') }}</label>
                                <input type="text" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][name_ar]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.name_ar">
                                @error("days.$dayIndex.activities.$activityIndex.name_ar")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="validationTooltip05">{{ __('app.plan.start-time') }}</label>
                                <input type="time" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][start_time]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.start_time">
                                @error("days.$dayIndex.activities.$activityIndex.start_time")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="validationTooltip06">{{ __('app.plan.end_time') }}</label>
                                <input type="time" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][end_time]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.end_time">
                                @error("days.$dayIndex.activities.$activityIndex.end_time")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="">{{ __('app.places') }}</label>
                                <select class="select2 form-control "
                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][place_id]"
                                    data-placeholder="{{ __('app.plan.choose...') }}"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.place_id">
                                    <option value="">{{ __('app.plan.select-one') }}</option>
                                    @foreach ($places as $place)
                                        <option value="{{ $place['id'] }}" wire:key="{{ $place['id'] }}">
                                            {{ $place['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error("days.$dayIndex.activities.$activityIndex.place_id")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="validationTooltip06">{{ __('app.plan.note-en') }}</label>
                                <input type="text" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][note_en]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.note_en">
                                @error("days.$dayIndex.activities.$activityIndex.note_en")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label" for="validationTooltip06">{{ __('app.plan.note-ar') }}</label>
                                <input type="text" class="form-control"

                                    name="days[{{ $dayIndex }}][activities][{{ $activityIndex }}][note_ar]"
                                    wire:model="days.{{ $dayIndex }}.activities.{{ $activityIndex }}.note_ar">
                                @error("days.$dayIndex.activities.$activityIndex.note_en")
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="m-4 ms-5">
                                <button type="button" class="btn btn-success"
                                    wire:click="addActivity({{ $dayIndex }})">+</button>
                                @if ($activityIndex > 0)
                                    <button type="button" class="btn btn-danger"
                                        wire:click="removeActivity({{ $dayIndex }}, {{ $activityIndex }})">-</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

        <div style="text-align: end">
            <button class="btn btn-primary" type="submit">{{ __('app.create') }}</button>
        </div>
    </form>
</div>
