import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import Checkbox from '@/Components/Checkbox';

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        content: '',
        auto_extract: false,
        continue_registration: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('articles.store'), {
            onSuccess: () => {
                if (data.continue_registration) {
                    setData('title', '');
                    setData('content', '');
                    setData('auto_extract', false);
                    setData('continue_registration', false);
                } else {
                    reset();
                }
            },
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    記事登録
                </h2>
            }
        >
            <Head title="記事登録" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-4">
                                    <InputLabel htmlFor="title" value="タイトル" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.title} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <div className="flex items-center mb-2">
                                        <Checkbox
                                            name="auto_extract"
                                            checked={data.auto_extract}
                                            onChange={(e) => setData('auto_extract', e.target.checked)}
                                        />
                                        <InputLabel htmlFor="auto_extract" value="HTMLから本文を自動抽出する" className="ml-2" />
                                    </div>
                                    <p className="text-sm text-gray-500 mb-2">
                                        {data.auto_extract
                                            ? 'HTMLソース全体を貼り付けると、本文部分のみを自動抽出します。'
                                            : '通常のテキスト入力です。'}
                                    </p>
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="content" value="本文" />
                                    <textarea
                                        id="content"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                        rows={25}
                                        style={{ minHeight: '500px' }}
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        required
                                        placeholder={
                                            data.auto_extract
                                                ? 'HTMLソースをここに貼り付けてください'
                                                : '記事の本文を入力してください。HTMLから自動抽出する場合は、上のチェックボックスをONにしてHTMLソース全体を貼り付けてください。'
                                        }
                                    />
                                    <InputError message={errors.content} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end space-x-4">
                                    <Link href={route('articles.index')}>
                                        <Button type="button" variant="secondary">
                                            キャンセル
                                        </Button>
                                    </Link>
                                    <label className="flex items-center">
                                        <Checkbox
                                            name="continue_registration"
                                            checked={data.continue_registration}
                                            onChange={(e) => setData('continue_registration', e.target.checked)}
                                        />
                                        <span className="ml-2 text-sm text-gray-600">続けて登録</span>
                                    </label>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? '保存中...' : '保存'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

