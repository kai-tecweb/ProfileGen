import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';

interface ClientsEditProps {
    client: {
        id: number;
        name: string;
        company: string | null;
        memo: string | null;
        questionnaire_text: string | null;
        answers_text: string | null;
    };
}

export default function Edit({ client }: ClientsEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        name: client.name || '',
        company: client.company || '',
        memo: client.memo || '',
        questionnaire_text: client.questionnaire_text || '',
        answers_text: client.answers_text || '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('clients.update', client.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    クライアント編集
                </h2>
            }
        >
            <Head title="クライアント編集" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit}>
                                <div className="mb-4">
                                    <InputLabel htmlFor="name" value="名前 *" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.name} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="company" value="会社名" />
                                    <TextInput
                                        id="company"
                                        type="text"
                                        className="mt-1 block w-full"
                                        value={data.company}
                                        onChange={(e) => setData('company', e.target.value)}
                                    />
                                    <InputError message={errors.company} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="memo" value="クライアント情報・特徴" />
                                    <textarea
                                        id="memo"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={5}
                                        value={data.memo}
                                        onChange={(e) => setData('memo', e.target.value)}
                                        placeholder="年齢層、性格、好み、ビジネススタイル、背景などを自由に記入してください"
                                    />
                                    <p className="text-sm text-gray-500 mt-1">
                                        クライアントの人物像を入力すると、より的確な提案が生成されます。
                                    </p>
                                    <InputError message={errors.memo} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="questionnaire_text" value="送付した質問テキスト" />
                                    <textarea
                                        id="questionnaire_text"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                                        rows={10}
                                        value={data.questionnaire_text}
                                        onChange={(e) => setData('questionnaire_text', e.target.value)}
                                        placeholder="クライアントに送った質問をここに記録できます"
                                    />
                                    <p className="text-sm text-gray-500 mt-1">
                                        質問マスター画面から「質問テキストをコピー」してここに貼り付けておくと便利です。
                                    </p>
                                    <InputError message={errors.questionnaire_text} className="mt-2" />
                                </div>

                                <div className="mb-4">
                                    <InputLabel htmlFor="answers_text" value="ヒアリング回答 *" />
                                    <textarea
                                        id="answers_text"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        rows={20}
                                        style={{ minHeight: '400px' }}
                                        value={data.answers_text}
                                        onChange={(e) => setData('answers_text', e.target.value)}
                                        required
                                        placeholder="クライアントから返ってきた回答をここに貼り付けてください。全ての質問に回答がなくても問題ありません。"
                                    />
                                    <p className="text-sm text-gray-500 mt-1">
                                        メール、LINE、チャット等で返ってきた回答をそのままコピー＆ペーストしてください。回答が不完全でも、AIが記事データから補完します。
                                    </p>
                                    <InputError message={errors.answers_text} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end space-x-4">
                                    <Link href={route('clients.show', client.id)}>
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

