<div class="mb-1" x-data="{download:false}">
    <div class="flex gap-2 justify-between items-center py-1.5">
        <label for="{{$idAttribute}}" class="text-sm text-gray-700 cursor-pointer select-none">{{$slot}}</label>
        <label for="{{$idAttribute}}" class="flex cursor-pointer select-none">
            <div class="relative flex">
                <input type="checkbox" id="{{$idAttribute}}" class="peer sr-only checkelement" @click="download=!download" {{ $attributes->has('checked') ? 'checked' : '' }} />
                <div class="block h-5 w-9 rounded-full bg-gray-200 peer-checked:bg-accent-500 transition-colors"></div>
                <div class="absolute w-[13px] h-[13px] transition bg-white rounded-full left-1 top-[3.5px] peer-checked:translate-x-full"></div>
            </div>
        </label>
    </div>
</div>
