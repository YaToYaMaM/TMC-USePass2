<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from "@inertiajs/vue3";

const props = defineProps<{
    report: {
        date: string;
        findings: string;
        time: string;
        location: string;
        actionTaken: string;
        guardName: string;
        teamLeader: string;
        departmentRepresentative: string;
        ssuHead: string;
    };
}>();

onMounted(() => {
    setTimeout(() => window.print(), 500);
});
</script>

<template>
    <Head title="Spot Report" />

    <div class="max-w-4xl mx-auto bg-white p-10 text-sm text-black leading-relaxed">
        <!-- Header with Logo and Titles -->
        <div class="text-center mb-4">
            <img src="/images/usep-logo-small.png" class="mx-auto w-16 mb-2" alt="USEP Logo" />
            <h1 class="text-2xl font-bold font-serif">University of Southeastern Philippines</h1>
            <h2 class="text-sm italic">Security Service Unit</h2>
        </div>

        <div class="relative text-center mb-4">

            <div>
                <h3 class="text-lg font-bold uppercase tracking-wide">SECURITY SPOT REPORT</h3>
                <p class="text-xs italic text-gray-600">
                    (For Security and Austerity Measures)<br />
                    Series of 2015
                </p>
            </div>

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
        </div>

        <div class="text-[15px]">
        <p class="mb-4"><strong>Date:</strong> <span class="underline">{{ report.date }}</span></p>
        <p class="mb-4 font-bold">TO WHOM IT MAY CONCERN:</p>

        <p class="mb-4">
            Sir/Madame;
            <br /><br />
            This is to inform your good office that as I conducted clearing inspection in my area of responsibility on or about
            <span class="underline font-semibold">{{ report.time }}</span> hours dated <span class="underline font-semibold">{{ report.date }}</span>,
            I found out that <span class="underline font-semibold">{{ report.findings }}</span> at <span class="underline font-semibold">{{ report.location }}</span>
            (specific department/office/building).
        </p>

        <p class="font-bold">ACTION TAKEN:</p>
        <p class="mb-6 underline font-semibold">{{ report.actionTaken }}</p>

        <p class="mb-6">
            This security spot report is subject for recording of the department or office concerned for further action.
        </p>
        </div>
        <div class="flex justify-between mt-10">

            <div class="text-center">
                <p class="text-left">Yours truly,</p>
                <br />
                <p class="underline text-lg font-semibold">{{ report.guardName }}</p>
                <p>(Signature Over Printed Name)</p>
                <p class="font-semibold">(Guard on Duty)</p>
            </div>
            <div class="text-center">
                <p class="text-left">Verified:</p>
                <br />
                <p class="underline text-lg font-semibold">{{ report.teamLeader }}</p>
                <p>(Signature Over Printed Name)</p>
                <p class="font-semibold">(Team Leader)</p>
            </div>
        </div>

        <div class="flex justify-between mt-10">
            <div>
                <p>Noted:</p>
                <br />
                <p class="underline text-lg font-semibold">{{report.ssuHead}}</p>
                <p class="font-semibold">Head, SSU</p>
                <p class="font-semibold">Date:____________________</p>
            </div>
            <div class="text-left">
                <p>Copy Received:</p>
                <br />
                <p class="underline text-lg font-semibold">{{ report.departmentRepresentative }}</p>
                <p class="font-semibold">Concerned Department/Office/Unit</p>
                <p class="font-semibold">Date:____________________</p>
            </div>
        </div>

        <div class="mt-10 border-t pt-4 text-xs">
            <div class="flex justify-between items-start">
                <div class="flex flex-col text-left max-w-md space-y-1">
                    <p class="font-semibold">
                        VISION:
                        <span class="font-normal "><strong>
          PREMIER RESEARCH UNIVERSITY TRANSFORMING COMMUNITIES IN THE ASEAN AND BEYOND </strong>
        </span>
                    </p>
                    <div class="flex flex-initial">
                    <p class="text-wrap text-left px-2">
                        University of Southeastern Philippines | Apokon, Tagum City, Davao del Norte 8100 www.usep.edu.ph
                    </p>
                    <p class="text-wrap text-left">
                        (084) 645-3050 local 501  www.usep.edu.ph useptagum@usep.edu.ph
                    </p>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <img src="/images/Picture3.png" alt="Logo2" class="h-20 w-auto object-contain" />
                    <img src="/images/Picture2.png" alt="Logo3" class="h-20 w-auto object-contain" />
                    <img src="/images/Picture4.png" alt="Logo4" class="h-20 w-auto object-contain" />
                </div>
            </div>
        </div>


    </div>
</template>

<style scoped>

</style>
