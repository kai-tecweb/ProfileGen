import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

interface QuestionsEditProps extends PageProps {
    question: {
        id: number;
        question: string;
        sort_order: number;
    };
}

export default function Edit({ question }: QuestionsEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        question: question.question,
        sort_order: question.sort_order,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('questions.update', question.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    質問編集
                </h2>
            }
        >
            <Head title="質問編集" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-4">
                                    <InputLabel htmlFor="question" value="質問文 *" />
                                    <textarea
                                        id="question"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={5}
                                        value={data.question}
                                        onChange={(e) => setData('question', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.question} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="sort_order" value="表示順" />
                                    <TextInput
                                        id="sort_order"
                                        type="number"
                                        className="mt-1 block w-full"
                                        value={data.sort_order}
                                        onChange={(e) => setData('sort_order', parseInt(e.target.value) || 0)}
                                    />
                                    <InputError message={errors.sort_order} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end space-x-4">
                                    <Link href={route('questions.index')}>
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
        </AuthenticatedLayout>
    );
}

