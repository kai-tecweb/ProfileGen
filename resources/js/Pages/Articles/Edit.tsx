import AdminV2Layout from '@/Layouts/AdminV2Layout';
import { Head, useForm, Link } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import Checkbox from '@/Components/Checkbox';

interface ArticlesEditProps {
    article: {
        id: number;
        title: string;
        content: string;
        image_urls?: string[] | null;
    };
}

export default function Edit({ article }: ArticlesEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        title: article.title,
        content: article.content,
        auto_extract: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('articles.update', article.id));
    };

    return (
        <AdminV2Layout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    記事編集
                </h2>
            }
        >
            <Head title="記事編集" />

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
                                            ? 'HTMLソース全体を貼り付けてください。本文が自動的に抽出されます。'
                                            : '通常のテキスト入力です。'}
                                    </p>
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="content" value="本文" />
                                    <textarea
                                        id="content"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        style={{
                                            minHeight: '500px',
                                            whiteSpace: 'pre-wrap',
                                            wordWrap: 'break-word',
                                            fontSize: '14px',
                                            lineHeight: '1.6',
                                            fontFamily: 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif',
                                        }}
                                        rows={20}
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        required
                                        placeholder={
                                            data.auto_extract
                                                ? 'HTMLソースをここに貼り付けてください'
                                                : '記事の本文を入力してください'
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
                                    <Button type="submit" disabled={processing}>
                                        {processing ? '更新中...' : '更新'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AdminV2Layout>
    );
}

