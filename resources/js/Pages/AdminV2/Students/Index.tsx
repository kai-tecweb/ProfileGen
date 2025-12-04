import AdminV2Layout from '@/Layouts/AdminV2Layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Student } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';
import Modal from '@/Components/Modal';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import TextInput from '@/Components/TextInput';
import Textarea from '@/Components/Textarea';
import { useState } from 'react';

interface StudentsIndexProps {
    students: Student[];
}

export default function Index({ students }: StudentsIndexProps) {
    const [showModal, setShowModal] = useState(false);
    const [selectedStudent, setSelectedStudent] = useState<Student | null>(null);

    const { data, setData, patch, processing, errors, reset } = useForm({
        student_status: 'active' as Student['student_status'],
        memo: '',
    });

    const handleEditStatus = (student: Student) => {
        setSelectedStudent(student);
        setData({
            student_status: student.student_status,
            memo: student.memo || '',
        });
        setShowModal(true);
    };

    const handleSubmitStatus = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedStudent) return;

        patch(route('admin.students.updateStatus', selectedStudent.id), {
            preserveScroll: true,
            onSuccess: () => {
                setShowModal(false);
                setSelectedStudent(null);
                reset();
                router.reload();
            },
        });
    };

    const getStatusBadge = (status: string) => {
        const badges = {
            pending: 'bg-yellow-100 text-yellow-800',
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
        };
        return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
    };

    const getStudentStatusBadge = (status: string) => {
        const badges = {
            active: 'bg-blue-100 text-blue-800',
            inactive: 'bg-gray-100 text-gray-800',
            banned: 'bg-red-100 text-red-800',
            no_contact: 'bg-orange-100 text-orange-800',
        };
        return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
    };

    const getStatusLabel = (status: string) => {
        const labels = {
            pending: '申請待ち',
            approved: '承認済み',
            rejected: '却下',
        };
        return labels[status as keyof typeof labels] || status;
    };

    const getStudentStatusLabel = (status: string) => {
        const labels = {
            active: '活動中',
            inactive: '退会',
            banned: '追放',
            no_contact: '音信不通',
        };
        return labels[status as keyof typeof labels] || status;
    };

    return (
        <AdminV2Layout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        生徒一覧
                    </h2>
                    <a
                        href={route('admin.students.pending')}
                        className="text-blue-600 hover:text-blue-800 underline"
                    >
                        申請待ち一覧 →
                    </a>
                </div>
            }
        >
            <Head title="生徒一覧" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {students.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Discord名
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    メールアドレス
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    承認状態
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    活動状況
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    承認日時
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    操作
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {students.map((student) => (
                                                <tr key={student.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {student.discord_name}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {student.email}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadge(
                                                                student.status
                                                            )}`}
                                                        >
                                                            {getStatusLabel(student.status)}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStudentStatusBadge(
                                                                student.student_status
                                                            )}`}
                                                        >
                                                            {getStudentStatusLabel(student.student_status)}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {student.approved_at
                                                            ? new Date(student.approved_at).toLocaleString('ja-JP')
                                                            : '-'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <Button
                                                            onClick={() => handleEditStatus(student)}
                                                            variant="secondary"
                                                        >
                                                            状況編集
                                                        </Button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8 text-gray-500">
                                    生徒が登録されていません
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={showModal} onClose={() => setShowModal(false)}>
                <form onSubmit={handleSubmitStatus}>
                    <div className="p-6">
                        <h2 className="text-lg font-medium text-gray-900 mb-4">
                            生徒状況編集: {selectedStudent?.discord_name}
                        </h2>

                        <div className="mt-4">
                            <InputLabel htmlFor="student_status" value="活動状況" />
                            <select
                                id="student_status"
                                value={data.student_status}
                                onChange={(e) =>
                                    setData('student_status', e.target.value as Student['student_status'])
                                }
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="active">活動中</option>
                                <option value="inactive">退会</option>
                                <option value="banned">追放</option>
                                <option value="no_contact">音信不通</option>
                            </select>
                            <InputError message={errors.student_status} className="mt-2" />
                        </div>

                        <div className="mt-4">
                            <InputLabel htmlFor="memo" value="メモ・備考" />
                            <Textarea
                                id="memo"
                                value={data.memo}
                                onChange={(e) => setData('memo', e.target.value)}
                                className="mt-1 block w-full"
                                rows={4}
                            />
                            <InputError message={errors.memo} className="mt-2" />
                        </div>

                        <div className="mt-6 flex justify-end space-x-3">
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setShowModal(false)}
                            >
                                キャンセル
                            </Button>
                            <Button type="submit" disabled={processing}>
                                {processing ? '保存中...' : '保存'}
                            </Button>
                        </div>
                    </div>
                </form>
            </Modal>
        </AdminV2Layout>
    );
}

