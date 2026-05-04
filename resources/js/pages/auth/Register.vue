<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

type SportOption = {
    id: number;
    name: string;
};

defineProps<{
    availableSports: SportOption[];
}>();
</script>

<template>
    <AuthBase
        title="Créer un compte"
        description="Enter les informations ci-dessous pour créer votre compte"
        >
            <Head title="S'inscrire" />

            <Form
                v-bind="store.form()"
                :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="first_name">Prénom</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        name="first_name"
                        placeholder="Celestin"
                    />
                    <InputError :message="errors.first_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="pseudo">Pseudo</Label>
                    <Input
                        id="pseudo"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="username"
                        name="pseudo"
                        placeholder="celestin"
                    />
                    <InputError :message="errors.pseudo" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Adresse email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="sex">Sexe</Label>
                    <select
                        id="sex"
                        name="sex"
                        required
                        :tabindex="4"
                        class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                    </select>
                    <InputError :message="errors.sex" />
                </div>

                <div class="grid gap-2">
                    <Label for="height">Taille (cm)</Label>
                    <Input
                        id="height"
                        type="number"
                        min="100"
                        max="250"
                        required
                        :tabindex="5"
                        name="height"
                        placeholder="175"
                    />
                    <InputError :message="errors.height" />
                </div>

                <div class="grid gap-2">
                    <Label for="weight">Poids actuel (kg)</Label>
                    <Input
                        id="weight"
                        type="number"
                        min="20"
                        max="500"
                        step="0.1"
                        required
                        :tabindex="6"
                        name="weight"
                        placeholder="72.5"
                    />
                    <InputError :message="errors.weight" />
                </div>

                <div class="grid gap-2">
                    <Label for="activity_level">Niveau d'activité</Label>
                    <select
                        id="activity_level"
                        name="activity_level"
                        required
                        :tabindex="7"
                        class="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        <option value="sedentary">Sédentaire</option>
                        <option value="light">Légère</option>
                        <option value="moderate" selected>Moderée</option>
                        <option value="active">Active</option>
                        <option value="very_active">Très active</option>
                    </select>
                    <InputError :message="errors.activity_level" />
                </div>

                <div class="grid gap-2">
                    <Label for="birth_date">Date de naissance</Label>
                    <Input
                        id="birth_date"
                        type="date"
                        required
                        :tabindex="8"
                        name="birth_date"
                    />
                    <InputError :message="errors.birth_date" />
                </div>

                <div class="grid gap-2">
                    <Label>Sports pratiques</Label>
                    <div class="grid gap-2 rounded-md border p-3">
                        <label
                            v-for="sport in availableSports"
                            :key="sport.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                name="sport_ids[]"
                                :value="sport.id"
                                class="h-4 w-4 accent-evo-black"
                            />
                            <span>{{ sport.name }}</span>
                        </label>
                    </div>
                    <InputError :message="errors.sport_ids" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Mot de passe</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="9"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmer le mot de passe</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="10"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="11"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Créer un compte
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Vous avez déja un compte ?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="12"
                    >Se connecter</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
