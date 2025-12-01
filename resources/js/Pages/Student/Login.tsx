import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('student.login'));
    };

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <Head title="学生ログイン" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">
                    学生ログイン
                </h2>

                <form onSubmit={submit}>
                    <div>
                        <InputLabel htmlFor="password" value="パスワード" />

                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full"
                            autoComplete="current-password"
                            isFocused={true}
                            onChange={(e) => setData('password', e.target.value)}
                        />

                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div className="mt-4 flex items-center justify-end">
                        <Button type="submit" disabled={processing}>
                            {processing ? 'ログイン中...' : 'ログイン'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

