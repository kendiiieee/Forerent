<div class="overflow-x-auto overflow-y-auto custom-scrollbar pr-2 max-h-[500px]">
    <table class="w-full min-w-[800px]">
        <thead class="sticky top-0 bg-white z-10">
            <tr class="text-left">
                {{ $head }}
            </tr>
            <tr>
                <td colspan="100" class="p-0">
                    <div class="border-b border-dashed border-blue-300"></div>
                </td>
            </tr>
        </thead>
        <tbody class="text-sm">
            {{ $body }}
        </tbody>
    </table>
</div>
