<script setup lang="ts">
import { onMounted } from 'vue';
import {Head} from "@inertiajs/vue3";

const props = defineProps<{
    report: {
        name: string;
        date: string;
        what: string;
        who: string;
        where: string;
        when: string;
        how: string;
        why: string;
        recommendation: string;
    };
}>();

onMounted(() => {
    setTimeout(() => window.print(), 500);
});
</script>

<template>
    <Head title="USePass" />
    <Head title="Print Report"/>
    <div v-if="props.report"  class="p-10 text-sm font-sans leading-relaxed text-black max-w-3xl mx-auto bg-white">
        <img src="/images/usep-logo-small.png" alt="Logo" class="mx-auto mb-2 w-20 h-auto">
        <h1 class="text-lg font-bold text-center mb-1 font-bookman">University of Southeastern Philippines</h1>
        <div class="relative text-center mb-4">
        <h2 class="text-center text-sm italic">Security Service Unit</h2>

            <div class="absolute top-0 right-0 border border-black text-xs p-2 w-44">
                <p class="font-bold text-center border-b border-black pb-1 mb-2">
                    FOR SSU USE ONLY
                </p>
                <div class="space-y-1">
                    <div>
                        <p>_________________________</p>
                        <p>Log No.</p>
                    </div>
                    <div>
                        <p>_________________________</p>
                        <p>Log Date</p>

                    </div>
                    <div>
                        <p>_________________________</p>
                        <p>Spot Report No.</p>

                    </div>
                    <div>
                        <p>_________________________</p>
                        <p>Department</p>

                    </div>
                </div>
            </div>
            <div>
        <h3 class="text-center font-bold text-lg mt-2 mb-16">INCIDENT REPORT</h3>
        <p class="text-left mb-4 text-base">Date: <span class="underline">{{ report.date }}</span></p>
            </div>

        </div>
        <p class="mb-4 font-bold">TO WHOM IT MAY CONCERN:</p>
        <p class="mb-4 mx-5">
            This is to submit my report with regards to the incident reported in my area of responsibility, to wit:
        </p>

        <div class="space-y-2">
            <p><strong>WHAT:</strong><span class="underline px-5 text-wrap">{{ report.what }}</span></p>
            <p><strong>WHO:</strong><span class="underline px-5 text-wrap"> {{ report.who }}</span></p>
            <p><strong>WHERE:</strong><span class="underline px-5 text-wrap"> {{ report.where }}</span></p>
            <p><strong>WHEN:</strong><span class="underline px-5 text-wrap"> {{ report.when }}</span></p>
            <p><strong>HOW:</strong><span class="underline px-5 text-wrap"> {{ report.how }}</span></p>
            <p><strong>WHY:</strong><span class="underline px-5 text-wrap"> {{ report.why }}</span></p>
            <p><strong>RECOMMENDATION:</strong><span class="underline px-5 text-wrap"> {{ report.recommendation }}</span></p>
        </div>

        <div class="flex justify-between mt-10">
            <div class="text-left">
                <p>Yours truly,</p>
                <br />
                <p class="underline text-xl">{{ report.name }}</p>
                <p>(Signature Over Printed Name)</p>
                <p><strong>Guard on Duty</strong></p>
            </div>
            <div class="text-right">
                <p>Information Validated:</p>
                <br />
                <p class="underline">_________________________</p>
                <p>(Signature Over Printed Name</p>
                <p><strong>Complainant</strong></p>
            </div>

        </div>
        <div class="flex justify-between mt-10">
            <div class="text-left">
                <p>Verified: </p>
                <br />
                <p class="underline">_________________________</p>

            </div>
            <div class="text-right">
                <p>Noted:</p>
                <br />
                <p class="underline">_________________________</p>
            </div>

        </div>
    </div>
</template>
<style>

</style>
