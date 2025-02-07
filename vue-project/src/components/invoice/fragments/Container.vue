<template>
    <div class="break-before-page max-w-3xl mx-auto p-6 bg-white rounded shadow-sm my-6" id="invoice">
        <slot name="header"></slot>
        <div class="-mx-4 mt-8 flow-root sm:mx-0">
            <table class="min-w-full">
                <slot name="table"></slot>
            </table>
        </div>
        <slot name="footer"></slot>
    </div>
</template>